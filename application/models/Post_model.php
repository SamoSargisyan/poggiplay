<?php

namespace Model;

use App;
use CI_Emerald_Model;
use Exception;
use stdClass;
use Model\Like_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Post_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'post';


    /** @var int */
    protected $user_id;
    /** @var string */
    protected $text;
    /** @var string */
    protected $img;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $comments;
    protected $likes;
    protected $user;

    protected $likes_count;

    public static function exists($id) {
        $count = App::get_ci()->s
            ->from(self::CLASS_TABLE)
            ->where('id', intval($id))
            ->count();
        return ($count > 0);
    }

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return string
     */
    public function get_text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function set_text(string $text)
    {
        $this->text = $text;
        return $this->save('text', $text);
    }

    /**
     * @return string
     */
    public function get_img(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function set_img(string $img)
    {
        $this->img = $img;
        return $this->save('img', $img);
    }


    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    // generated

    /**
     * @return int
     */
    public function get_likes_count(): int
    {
        if (empty($this->likes_count)) {
            $this->likes_count = Like_model::get_count_by_post_id($this->id);
        }
        return $this->likes_count;
    }

    /**
     * @param int $likes_count
     *
     * @return bool
     */
    public function set_likes_count(int $likes_count)
    {
        $this->likes_count = $likes_count;
        return $this->save('likes_count', $likes_count);
    }

    /**
     * @return mixed
     */
    public function get_likes()
    {
        if (empty($this->likes)) {
            try {
                $this->likes = Like_model::get_all_by_post_id($this->id);
            } catch (\Exception $e) {
                $this->likes = [];
            }
        }
        return $this->likes;
    }

    /**
     * @return Comment_model[]
     */
    public function get_comments()
    {
        $this->is_loaded(TRUE);

        if (empty($this->comments))
        {
            $this->comments = Comment_model::get_all_by_assign_id($this->get_id());
        }
        return $this->comments;

    }

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user))
        {
            try
            {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception)
            {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    function __construct(?int $id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return (App::get_ci()->s->get_affected_rows() > 0);
    }

    public function comment($user_id, $post_id, $message, $parent_id = 0){
        App::get_ci()->s->from(Comment_model::CLASS_TABLE)
            ->insert([
                'user_id' => $user_id,
                'assign_id' => $post_id,
                'text' => $message,
                'parent_id' => $parent_id
            ])
            ->execute();

        return new static(App::get_ci()->s->get_insert_id());
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function get_all()
    {

        $data = App::get_ci()->s->from(self::CLASS_TABLE)->many();
        $ret = [];
        foreach ($data as $i)
        {
            $ret[] = (new self())->set($i);
        }
        return $ret;
    }

    /**
     * @param Post_model|Post_model[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'main_page':
                return self::_preparation_main_page($data);
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Post_model[] $data
     * @return stdClass[]
     */
    private static function _preparation_main_page($data)
    {
        $ret = [];

        foreach ($data as $d){
            $o = new stdClass();

            $o->id = $d->get_id();
            $o->img = $d->get_img();

            $o->text = $d->get_text();

            $o->user = User_model::preparation($d->get_user(),'main_page');

            $o->time_created = $d->get_time_created();
            $o->time_updated = $d->get_time_updated();

            $ret[] = $o;
        }


        return $ret;
    }


    /**
     * @param Post_model $data
     * @return stdClass
     */
    private static function _preparation_full_info(Post_model $data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->img = $data->get_img();

        $o->user = User_model::preparation($data->get_user(),'main_page');
        $o->comments = Comment_model::preparation($data->get_comments(),'full_info');

        $o->likes = $data->get_likes_count();

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        $ret[] = $o;


        return $o;
    }


}
