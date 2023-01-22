<?php
DI::rest()->get('/ping', function() {
	http(200, 'pong');
});
