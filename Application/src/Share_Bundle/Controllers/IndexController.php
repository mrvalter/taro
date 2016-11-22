<?php
namespace Share_Bundle\Controllers;

use Kernel\Classes\Controller;

class IndexController extends Controller {
    
    public function indexAction(int $root=0, $id='kop')
    {					
		
        return $this->render('hello', ['root'=>$root, 'id'=>$id]);
    }		
}
