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
    $created_at = date('Y-m-d H:i:s');
    
    // Store message in your database (you can use RedBeanPHP here)
    $message = R::dispense('messages');
    $message->sender_id = $sender_id;
    $message->receiver_id = $receiver_id;
    $message->sender_type = $sender_type;
    $message->receiver_type = $receiver_type;
    $message->content = $content; // Make sure 'message' key is present in the request body
    $message->created_at = $created_at;
    R::store($message);

    // // ...

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
    
    http(200);
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
    $messages = array_values($messages); 
    http(200, $messages, true);
}, ['auth.loggedIn']);