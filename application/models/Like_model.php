<?php

namespace Model;
use App;
use CI_Emerald_Model;
use Exception;

class Like_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'post_like';

    /** @var int */
    protected $user_id;
    /** @var int */
    protected $post_id;
    /** @var int */
    protected $amount;

    /** @var string */
    protected $time_created;

    // generated
    protected $post;
    protected $user;


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
     * @return int
     */
    public function get_post_id(): int
    {
        return $this->post_id;
    }

    /**
     * @param int $post_id
     *
     * @return bool
     */
    public function set_post_id(int $post_id)
    {
        $this->post_id = $post_id;
        return $this->save('post_id', $post_id);
    }

    /**
     * @param $post_id
     * @return self[]
     */
    public static function get_all_by_post_id($post_id)
    {
        $likes = App::get_ci()
            ->s
            ->from(self::CLASS_TABLE)
            ->where([
                'post_id' => $post_id
            ])
            ->orderBy('time_created','ASC')
            ->many();

        $result = [];
        foreach ($likes as $like)
        {
            $result[] = (new self())->set($like);
        }
        return $result;
    }

    /**
     * @param $post_id
     * @return int
     */
    public function get_count_by_post_id($post_id): int
    {
        return App::get_ci()
            ->s
            ->from(self::CLASS_TABLE)
            ->where([
                'post_id' => $post_id
            ])
            ->orderBy('time_created','ASC')
            ->count();
    }

    /**
     * @param int $amount
     *
     * @return bool
     */
    public function set_amount(int $amount)
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
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

    // generated

    /**
     * @return Post_model
     */
    public function get_post(): Post_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->post)) {
            $this->post = new Post_model($this->get_post_id());
        }
        return $this->post;
    }

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user)) {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception) {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    public function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public static function create(array $data)
    {
        App::get_ci()->s
            ->from(self::CLASS_TABLE)
            ->insert($data)
            ->execute();

        return new static(
            App::get_ci()
                ->s
                ->get_insert_id()
            );
    }

    public function delete()
    {
        App::get_ci()
            ->s
            ->from(self::CLASS_TABLE)
            ->where([
                'id' => $this->get_id()
            ])
            ->delete()
            ->execute();

        return (
            App::get_ci()
                ->s
                ->get_affected_rows() > 0
        );
    }

}