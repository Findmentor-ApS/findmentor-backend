<?php 
require_once __DIR__ . '/../pusher_config.php';
DI::rest()->post('/message/send_message', function (RestData $data) use ($pusher) {
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    // var_dump($body);
    // die();
    $content = $body['content'];
    $sender_id = $body['sender_id'];
    $receiver_id = $body['receiver_id'];
    $sender_type = $body['sender_type'];
    $receiver_type = $body['receiver_type'];
    $seen = $body['seen'];
    $created_at = date('Y-m-d H:i:s');
    
    // Store message in your database (you can use RedBeanPHP here)
    $message = R::dispense('messages');
    $message->sender_id = $sender_id;
    $message->receiver_id = $receiver_id;
    $message->sender_type = $sender_type;
    $message->receiver_type = $receiver_type;
    $message->content = $content; // Make sure 'message' key is present in the request body
    $message->created_at = $created_at;
    $message->seen = $seen;
    R::store($message);

    $channelName = createChannelName($sender_type, $sender_id, $receiver_type, $receiver_id);
    $pusher->trigger($channelName, 'new-message', $body);

    $updatedContactsSender = getContacts($sender_id, $sender_type);

    $pusher->trigger($sender_type . '-' . $sender_id . '-contacts-channel', 'update-contacts', [
        'updated_contacts' => $updatedContactsSender
    ]);
    $updatedContactsReceiver = getContacts($receiver_id, $receiver_type);

    $pusher->trigger($receiver_type . '-' . $receiver_id .'-contacts-channel', 'update-contacts', [
        'updated_contacts' => $updatedContactsReceiver
    ]);
    
    http(200, $message, true);
}, ['auth.loggedIn']);

DI::rest()->get('/message/get_contacts', function (RestData $data) use ($pusher){
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $contacts = getContacts($user['id'], $usertype);

    http(200, $contacts, true);
}, ['auth.loggedIn']);




// generate get_messages_for_contact
DI::rest()->get('/message/get_messages_for_contact/:targetUserType/:targetUserId', function (RestData $data) {
    $user = $data->middleware['user'];
    $targetUserType = $data->pathdata['targetUserType'];
    $targetUserId = (int)$data->pathdata['targetUserId'];

    $page =(int)$data->request->getQuery()['page'] ?? 0;
    $perPage = (int) 20;
    $offset = (int) ($page * $perPage);
    $messages = R::find(
        'messages',
        "((sender_id = ? AND receiver_id = ? AND sender_type = ? AND receiver_type = ?) OR
          (sender_id = ? AND receiver_id = ? AND sender_type = ? AND receiver_type = ?))
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?",
        [
            $user['id'], $targetUserId, $data->middleware['usertype'], $targetUserType,
            $targetUserId, $user['id'], $targetUserType, $data->middleware['usertype'],
            $perPage, $offset
        ]
    );

    // Mark the messages as seen
    foreach ($messages as $message) {
        // if receiver_id and receiver_type is this user, set message seen to true
        if ($message->receiver_id === $user['id'] && $message->receiver_type === $data->middleware['usertype']) {
            $message->seen = true;
            R::store($message);
        }
    }
    

    $messages = array_values($messages); 
    http(200, $messages, true);
}, ['auth.loggedIn']);


DI::rest()->post('/message/mark_messages_as_seen', function (RestData $data) use ($pusher) {
    $body = $data->request->getBody();
    $messageIds = $body['message_ids'];
    $sender_type = $body['sender_type'];
    $sender_id = $body['sender_id'];
    $receiver_type = $body['receiver_type'];
    $receiver_id = $body['receiver_id'];
    $messages = [];
    // Mark the messages as seen in your database and add it to messages array
    foreach ($messageIds as $messageId) {
        $message = R::load('messages', $messageId);
        $message->seen = true;
        $messages[] = $message;
        R::store($message);
    }

    // Trigger a Pusher event on the existing channel
    $channelName = createChannelName($sender_type, $sender_id, $receiver_type, $receiver_id);
    $pusher->trigger($channelName, 'message-seen', [
        'message_ids' => $messageIds
    ]);

    $messages= array_values($messages);
    http(200, $messages, true);
}, ['auth.loggedIn']);
