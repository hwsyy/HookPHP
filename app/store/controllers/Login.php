<?php
use Yaf\Session;
use Hook\Http\Header, Hook\Db\PdoConnect;

class LoginController extends InitController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_view->referer = $this->getRequest()->getQuery('referer', '/');
    }

    public function postAction()
    {
        $user = $this->getRequest()->getPost('user');
        $pass = $this->getRequest()->getPost('pass');
        $referer = $this->getRequest()->getPost('referer', '/');
        
        $login = PdoConnect::getInstance()->fetch(
            Hook\Sql\Login::SQL_LOGIN,
            [$user, $this->pass($user, $pass)]
        );
        
        if ($login) {
            $login['security'] = [
                'ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                'token' => md5(uniqid(mt_rand(), true)),
                'agent' => $this->getRequest()->getServer('HTTP_USER_AGENT'),
                'time' => time()
            ];
            Session::getInstance()->set('user', $login);
            Header::redirect($referer);
            return true;
        }
        
        $this->_view->error = [l('login.fail')];
    }

    public function outAction()
    {
        Session::getInstance()->del('user');
        Header::redirect('/');
        return true;
    }

    public static function pass($user, $pass)
    {
        return strrev(md5(strrev(md5($pass . $user . $pass) . md5($pass . $pass . $user))) . md5(md5($pass . $user . $pass) . md5($pass . $pass . $user)));
    }
}