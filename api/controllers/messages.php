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

    $channelName = createChannelName($sender_type, $sender_id, $receiver_type, $receiver_id);
    $pusher->trigger($channelName, 'new-message', $body);
    
    http(200);
}, ['auth.loggedIn']);

// // GEt messages by id and type
// DI::rest()->get('/message/get_messages/:targetUserType/:targetUserId', function (RestData $data) {
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


DI::rest()->get('/message/get_contacts', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    // Get contacts
    $contacts = R::getAll(
        "SELECT
            CASE
                WHEN m.sender_id = ? THEN m.receiver_id
                ELSE m.sender_id
            END as contact_id,
            CASE
                WHEN m.sender_id = ? THEN m.receiver_type
                ELSE m.sender_type
            END as contact_type,
            CASE
                WHEN m.sender_id = ? THEN
                    CASE
                        WHEN m.receiver_type = 'mentor' THEN mentor.first_name
                        WHEN m.receiver_type = 'commune' THEN commune.first_name
                        ELSE usr.first_name
                    END
                ELSE
                    CASE
                        WHEN m.sender_type = 'mentor' THEN mentor.first_name
                        WHEN m.sender_type = 'commune' THEN commune.first_name
                        ELSE usr.first_name
                    END
            END as first_name,
            CASE
                WHEN m.sender_id = ? THEN
                    CASE
                        WHEN m.receiver_type = 'mentor' THEN mentor.last_name
                        WHEN m.receiver_type = 'commune' THEN commune.last_name
                        ELSE usr.last_name
                    END
                ELSE
                    CASE
                        WHEN m.sender_type = 'mentor' THEN mentor.last_name
                        WHEN m.sender_type = 'commune' THEN commune.last_name
                        ELSE usr.last_name
                    END
            END as last_name,
            MAX(m.created_at) AS last_message_at,
            last_msg.content AS last_message_content
        FROM messages AS m
        LEFT JOIN messages AS last_msg
            ON last_msg.created_at = (
                SELECT MAX(created_at)
                FROM messages
                WHERE (
                    (sender_id = m.sender_id AND sender_type = m.sender_type AND receiver_id = m.receiver_id AND receiver_type = m.receiver_type)
                    OR
                    (sender_id = m.receiver_id AND sender_type = m.receiver_type AND receiver_id = m.sender_id AND receiver_type = m.sender_type)
                )
            )
        LEFT JOIN mentor ON (m.sender_id = mentor.id AND m.sender_type = 'mentor') OR (m.receiver_id = mentor.id AND m.receiver_type = 'mentor')
        LEFT JOIN commune ON (m.sender_id = commune.id AND m.sender_type = 'commune') OR (m.receiver_id = commune.id AND m.receiver_type = 'commune')
        LEFT JOIN user AS usr ON (m.sender_id = usr.id AND m.sender_type = 'user') OR (m.receiver_id = usr.id AND m.receiver_type = 'user')
        WHERE (m.sender_id = ? AND m.sender_type = ?) OR (m.receiver_id = ? AND m.receiver_type = ?)
        GROUP BY contact_id, contact_type, first_name, last_name, last_message_content
        ORDER BY last_message_at DESC",
        [$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $usertype, $user['id'], $usertype]
    );
    

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