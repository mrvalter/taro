<?php
namespace Share_Bundle\Controllers;

use Kernel\Classes\Controller;

class IndexController extends Controller {
    
    public function indexAction(int $root=0, $id='kop')
    {	
		
		$fp = fopen('php://temp', 'w+');
		$body = new \GuzzleHttp\Psr7\Stream($fp);
		$body->write('I am a young man');
		$body->write('<h1>HELLO MAN</h1>');

		$response = new \Kernel\Services\HttpFound\Response(200, [], $body);
		

		echo $t;
		die();
		
        return $this->render('hello', ['root'=>$root, 'id'=>$id]);
    }		
}
