<?php

use Model\Like_model;
use Model\Login_model;
use Model\Post_model;
use Model\User_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function comment(){ // or can be App::get_ci()->input->post('news_id') , but better for GET REQUEST USE THIS ( tests )

        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $data = json_decode(file_get_contents('php://input'));

        $post_id = intval($data->id);
        $message = $data->text;

        if (empty($post_id) || empty($message)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $comment = $post->comment(User_model::get_user()->get_id(), $post_id, $message);

        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }

    public function login()
    {
        // Right now for tests we use from controller
        // это с формы получаем данные
        $login = App::get_ci()->input->post('login');
        $password = App::get_ci()->input->post('password');

        // But data from modal window sent by POST request.  App::get_ci()->input...  to get it.
        // функцию поиска вынесли в другой класс, что б не захламлять логику

        if (empty($login) || empty($password)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        //Todo: 1 st task - Authorisation.

        // а здесь проверяем с бд
        try {
            $user = Login_model::login($login, $password);

        // если введенный пароль не соответствует паролю с бд - будет ошибка
            if ($user->get_password() !== $password) {
                return $this->response_error('Auth failed');
            }

        } catch (EmeraldModelNoDataException $e){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        // можно было и обойтись, но раз уже есть такой метод, то воспользуемся
        Login_model::start_session($user);
        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money(){
        // todo: 4th task  add money to user logic
        return $this->response_success(['amount' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

    public function buy_boosterpack(){
        // todo: 5th task add money to user logic
        return $this->response_success(['amount' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }


    public function like ($post_id)
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        if (empty($type) || is_null($post_id) || !intval($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $object = null;

        if (!Post_model::exists($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        } else {
            $object = new Post_model($post_id);
        }

        Like_model::create([
            'user_id' => User_model::get_session_id(),
            'post_id' => $post_id
        ]);
        $object->reload();

        return $this->response_success([
            'likes' => $object->get_likes_count(),
        ]);
    }

}
