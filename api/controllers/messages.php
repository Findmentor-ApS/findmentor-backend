<?php 
DI::rest()->get('/messages/:targetUserType/:targetUserId', function (RestData $data) {
    $user = $data->middleware['user'];
    $targetUserType = $data->pathdata['targetUserType'];
    $targetUserId = (int)$data->pathdata['targetUserId'];

    $page =(int)$data->request->getQuery('page') ?? 0;
    $perPage = 10;
    
    $offset = $page * $perPage;
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
    foreach ($messages as $message) {
        $message['info'] = R::findOne($targetUserType, 'id = ?', [$targetUserId]);
    }

    http(200, $messages, true);
}, ['auth.loggedIn']);

DI::rest()->post('/messages/:targetUserType/:targetUserId', function (RestData $data) {
    $user = $data->middleware['user'];
    $targetUserType = $data->pathdata['targetUserType'];
    $targetUserId = $data->pathdata['targetUserId'];
    $body = $data->request->getBody();

    $message = R::dispense('messages');
    $message->sender_id = $user['id'];
    $message->receiver_id = $targetUserId;
    $message->sender_type = $data->middleware['usertype'];
    $message->receiver_type = $targetUserType;
    $message->content = $body['message']; // Make sure 'message' key is present in the request body
    $message->created_at = date('Y-m-d H:i:s');
    R::store($message);

    http(200, true);
}, ['auth.loggedIn']);
