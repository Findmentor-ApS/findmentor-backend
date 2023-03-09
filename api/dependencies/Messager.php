<?php


class Messager
{
    public function __construct()
    {
    }

    public function send($channel, $event, $data)
    {
      $options = array(
        'cluster' => 'eu',
        'useTLS' => true
      );
      $pusher = new Pusher\Pusher(
          'dbdb62837648a19fb31a',
          '6d7d8c81cb4152a2f718',
          '1565531',
          $options
      );
      $pusher->trigger($channel, $event, $data);
    }
}
?>