<?php 
// DI::rest()->get('/messages/:targetUserType/:targetUserId', function (RestData $data) {
//     $user = $data->middleware['user'];
//     $targetUserType = $data->pathdata['targetUserType'];
//     $targetUserId = (int)$data->pathdata['targetUserId'];

//     $page =(int)$data->request->getQuery('page') ?? 0;
//     $perPage = 10;
    
//     $offset = $page * $perPage;
//     $messages = R::find(
//         'messages',
//         "((sender_id = ? AND receiver_id = ? AND sender_type = ? AND receiver_type = ?) OR
//           (sender_id = ? AND receiver_id = ? AND sender_type = ? AND receiver_type = ?))
//          ORDER BY created_at DESC
//          LIMIT ? OFFSET ?",
//         [
//             $user['id'], $targetUserId, $data->middleware['usertype'], $targetUserType,
//             $targetUserId, $user['id'], $targetUserType, $data->middleware['usertype'],
//             $perPage, $offset
//         ]
//     );
//     foreach ($messages as $message) {
//         $message['info'] = R::findOne($targetUserType, 'id = ?', [$targetUserId]);
//     }

//     http(200, $messages, true);
// }, ['auth.loggedIn']);

// DI::rest()->post('/messages/:targetUserType/:targetUserId', function (RestData $data) {
//     $user = $data->middleware['user'];
//     $targetUserType = $data->pathdata['targetUserType'];
//     $targetUserId = $data->pathdata['targetUserId'];
//     $body = $data->request->getBody();

//     $message = R::dispense('messages');
//     $message->sender_id = $user['id'];
//     $message->receiver_id = $targetUserId;
//     $message->sender_type = $data->middleware['usertype'];
//     $message->receiver_type = $targetUserType;
//     $message->content = $body['message']; // Make sure 'message' key is present in the request body
//     $message->created_at = date('Y-m-d H:i:s');
//     R::store($message);

//     http(200, true);
// }, ['auth.loggedIn']);

require_once __DIR__ . '/../pusher_config.php';
DI::rest()->post('/message/send_message', function (RestData $data) use ($pusher) {
    $body = $data->request->getBody();
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

    // // Broadcast the message via Pusher
    $pusher->trigger('chat-channel', 'new-message', $body);
    
    http(200);
});

DI::rest()->get('/message/get_messages', function () {
    // Fetch messages from your database (you can use RedBeanPHP here)
    $messages = R::findAll('messages');
    $messagesArray = array_values((array) $messages);
    // Return messages as JSON
    http(200, $messagesArray, true);
});