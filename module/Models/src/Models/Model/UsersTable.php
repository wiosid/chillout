<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class UsersTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function fetchAll() {
        $select = new Select($this->tableGateway->table);
        $select->order('fld_updated_datetime DESC');
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }

    public function totalUsersCount() {
        $select = new Select($this->tableGateway->table);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function todayActiveUsersCount() {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table);
        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.fld_updated_datetime', Predicate\Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, strtotime(date('Y-m-d 00:00:00',time()))),
                            )
                    )
                )
        );
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function todayRegisteredUsersCount() {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table);
        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.fld_created_datetime', Predicate\Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, strtotime(date('Y-m-d 00:00:00',time()))),
                            )
                    )
                )
        );
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function getUserById($id) {
        $id = (int) $id;

        $rowset = $this->tableGateway->select(array(
            'user_id' => $id));

        $row = $rowset->current();

        return $row;
    }

    public function isValidUser($id) {
        $id = (int) $id;

        $rowset = $this->tableGateway->select(array(
            'user_id' => $id,
            'fld_is_blocked' => 0
                )
        );

        $row = $rowset->current();

        return (!empty($row)) ? TRUE : FALSE;
    }

    public function getUserDetails($id) {

        $q = 'SELECT  
            `user_id` as id,
            `username`,
            `password`,
            `fld_name`,
            `email`,
            `fld_profile_photo`,
            `fld_profile_photo_width`,
            `fld_profile_photo_height`,
            `fld_age`,
            `fld_gender`,
            `fld_location`,
            `fld_bio`,
            `fld_updated_datetime`,
            `fld_college`,
            `fld_status`
            FROM users
            WHERE user_id="' . $id . '"
                    ';
        return $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_ASSOC);
    }

    public function getUserSettings($id) {

        $q = 'SELECT  
            `fld_gender`,
            `fld_search_distance`,
            `fld_min_age`,
            `fld_max_age`,
            `fld_looking_for`,
            `fld_notification_status`
            FROM users
            WHERE user_id="' . $id . '"
                    ';
        return $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_ASSOC);
    }

    public function getUserProfileDetails($id) {

        $q = 'SELECT  
            `user_id` as id,
            `fld_name`,
            `username`,
            `fld_profile_photo`,
            `fld_profile_photo_width`,
            `fld_profile_photo_height`,
            `fld_age`,
            `fld_gender`,
            `fld_location`,
            `fld_bio`,
            `fld_updated_datetime`,
            `fld_college`
            FROM users
            WHERE user_id="' . $id . '"
                    ';
        return $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_ASSOC);
    }

    public function saveUser($data) {
        $id = (int) $data['user_id'];
        if (!empty($data['password'])) {
            $bcrypt = new Bcrypt();
            $data['password'] = $bcrypt->create($data['password']);
        }
        if ($id == 0) {
            $data['fld_created_datetime'] = time();
            $data['fld_updated_datetime'] = time();
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            $data['fld_updated_datetime'] = time();
            if ($this->getUserById($id)) {
                $this->tableGateway->update($data, array('user_id' => $id));
            } else {
                throw new \Exception('Invalid user');
            }
        }
        return $id;
    }

    public function save(User $user) {
        $pass = $user->getPassword();

        if (!empty($pass)) {
            $user->setPassword($pass);
            $bcrypt = new Bcrypt();
            $bcrypt->setCost();
            $password = $bcrypt->create($user->getPassword());
        }



        $data = array(
            'fld_name' => $user->getDisplayName(),
            'email' => $user->getEmail(),
            'password' => $password,
            'fld_status' => 1,
            'fld_created_datetime' => time()
        );

        $id = (int) $user->getId();

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getUserById($id)) {
                unset($data['fld_created_on']);
                if (empty($data['password'])) {
                    unset($data['password']);
                }

                $this->tableGateway->update($data, array('user_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $id;
    }

    public function deleteUser($id) {
        $this->tableGateway->delete(array('user_id' => $id));
    }

    /**
     * Function isFbUserOnStupidCupid() Implemented for 
     * checking facebook user is on StupidCupid with facebook user id
     * 
     * @param type $param
     * 
     * @return true if subscribed or false as boolean 
     */
    public function isFbUserOnStupidCupid($fbId = Null) {
        $id = (int) $id;

        $rowset = $this->tableGateway->select(array(
            'username' => $fbId));

        $row = $rowset->current();

        return $row->user_id;
    }

    /**
     * Function updateUserAcceptCount() Implemented for 
     * updating user accept count
     * 
     */
    public function updateUserAcceptCount($fldUserId) {
        $this->tableGateway->update(array('fld_accept_count' => new \Zend\Db\Sql\Predicate\Expression('fld_accept_count + 1')), array('user_id' => $fldUserId));
    }

    /**
     * Function getUserMatchStatus() Implemented for 
     * updating how much user profile is matched
     * 
     */
    public function getUserMatchStatus($fldUserId, $fldOtherUserId) {
        return 0;
//        $userAnswers = $this->getUserAnswers($fldUserId);
//        $q = 'SELECT 
//            ( (percentage/fld_lowest_rank)/(fld_highest_rank/fld_lowest_rank))*100 as normalized_rank 
//            from (
//                SELECT 
//                id,
//                IFNULL(MATCH (fld_answers) AGAINST ("' . $userAnswers . '" IN BOOLEAN MODE)*100/' . $this->getSameQuestionsCount($fldUserId, $fldOtherUserId) . ',0) as percentage,
//                fld_highest_rank,
//                fld_lowest_rank
//                FROM `users`
//                WHERE id =' . $fldOtherUserId . '
//            ) a1';
        $q = 'SELECT 
                IFNULL( (
                    (
                    SUM( IFNULL( ! ( `ur1`.`fld_answer_id` ^ `ur2`.`fld_answer_id` ) , 0 ) ) *100 / SUM( IFNULL( ! ( `ur1`.`fld_question_id` ^ `ur2`.`fld_question_id` ) , 0 ) ) - fld_lowest_rank ) *100 / ( fld_highest_rank - fld_lowest_rank )
                    ), 0
                    ) AS normalized_rank
                FROM `users` `usr`
                FORCE INDEX ( fld_accept_count, fld_updated_datetime )
                LEFT JOIN tbl_user_responses ur1 ON ( `usr`.`user_id` = ur1.fld_user_id )
                LEFT JOIN tbl_user_responses ur2 ON ( `ur1`.`fld_question_id` = ur2.fld_question_id
                AND ur2.fld_user_id =' . $fldUserId . ' )
                LEFT JOIN `tbl_user_hits` `uh` ON (`usr`.`user_id` = `uh`.`fld_other_user_id` AND `uh`.`fld_user_id`=' . $fldUserId . ')
                LEFT JOIN `tbl_block_users` `bu` ON ( (`usr`.`user_id` = `bu`.`fld_other_user_id` AND `bu`.`fld_user_id`=' . $fldUserId . ') OR (`usr`.`user_id` = `bu`.`fld_user_id` AND `bu`.`fld_other_user_id`=' . $fldUserId . '))
                WHERE `usr`.`user_id` =' . $fldOtherUserId . ' AND `uh`.`user_id` IS NULL AND `bu`.`user_id` IS NULL
                GROUP BY `usr`.`user_id`
                LIMIT 1';
        $normalizedRank = (int) $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
        switch (true) {
            case ($normalizedRank >= 70):
                return 2;
                break;
            case ($normalizedRank >= 30 && $normalizedRank < 70):
                return 1;
                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * Function getUserAnswers() Implemented for 
     * getting user answers
     * 
     */
//    public function getUserAnswers($fldUserId) {
//        $q = 'SELECT 
//            fld_answers
//                FROM `users`
//                WHERE id =' . $fldUserId . '
//            ';
//        return (string) $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
//    }

    /**
     * Function getUserQuestions() Implemented for 
     * getting user questions
     * 
     */
//    public function getUserQuestions($fldUserId) {
//        $q = 'SELECT 
//            fld_questions
//                FROM `users`
//                WHERE id =' . $fldUserId . '
//            ';
//        return (string) $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
//    }

    /**
     * Function getSameQuestionsCount() Implemented for 
     * getting same question count of two users
     * 
     */
//    public function getSameQuestionsCount($fldUserId, $fldOtherUserId) {
//        $q = 'SELECT 
//            fld_questions
//                FROM `users`
//                WHERE id ="' . $fldUserId . '"
//            ';
//        $fldUserQuestions = explode(" ", $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_COLUMN));
//        $q = 'SELECT 
//            fld_questions
//                FROM `users`
//                WHERE id ="' . $fldOtherUserId . '"
//            ';
//        $fldOtherUserQuestions = explode(" ", $this->tableGateway->getAdapter()->query($q)->execute()->getResource()->fetch(\PDO::FETCH_COLUMN));
//        return $count = count(array_intersect($fldUserQuestions, $fldOtherUserQuestions));
//    }

    /**
     * Function updateUserRanks() Implemented for 
     * updating user ranks
     * 
     */
    public function updateUserRanks($fldUserIds) {
        if (is_array($fldUserIds) && !empty($fldUserIds)) {
            $q = 'UPDATE `users` SET
                `fld_highest_rank` = `get_user_max_percentage`(user_id),
                `fld_lowest_rank` = `get_user_min_percentage`(user_id)
                WHERE user_id IN (' . implode(",", $fldUserIds) . ')
                ;';
            $this->tableGateway->getAdapter()->query($q)->execute();
        }
    }

    /**
     * Function getMatchesList() Implemented for 
     * getting best matched users data
     * 
     */
    public function getMatchesList($fldUserId, $query, $notInUserIds = array()) {
        $rank = (int) $query['rank'];
        $length = (int) $query['length'];
        $length = !empty($length) ? $length : 10;
        $select = new Select($this->tableGateway->table);
        $select->columns(
                array(
                    'id' => 'user_id',
                    'fld_name' => 'fld_name',
                    'username' => 'username',
                    'fld_profile_photo' => 'fld_profile_photo',
                    'fld_profile_photo_width' => 'fld_profile_photo_width',
                    'fld_profile_photo_height' => 'fld_profile_photo_height',
                    'fld_age' => 'fld_age',
                    'fld_gender' => 'fld_gender',
                    'fld_location' => 'fld_location',
                    'fld_bio' => 'fld_bio',
                    'fld_updated_datetime' => 'fld_updated_datetime',
//                    'fld_interests' => 'fld_interests',
//                    'fld_friends' => 'fld_friends',
                    'fld_college' => 'fld_college'
                )
        );

//        $friendsOnCondition = new \Zend\Db\Sql\Predicate\Expression('(' . $this->tableGateway->table . '.user_id = tbl_friends.fld_user_id AND tbl_friends.fld_other_user_id =' . $fldUserId . ') OR (' . $this->tableGateway->table . '.user_id = tbl_friends.fld_other_user_id AND tbl_friends.fld_user_id =' . $fldUserId . ')');
//        $select->join(array('tbl_friends' => 'tbl_friends'), $friendsOnCondition, array(), Select::JOIN_LEFT);
//        $select->where(
//                new Predicate\IsNull('tbl_friends.id')
//        );
//        $hitsOnCondition = new \Zend\Db\Sql\Predicate\Expression('(' . $this->tableGateway->table . '.user_id = tbl_user_hits.fld_user_id AND tbl_user_hits.fld_other_user_id =' . $fldUserId . ')');
//        $select->join(array('tbl_user_hits' => 'tbl_user_hits'), $hitsOnCondition, array(), Select::JOIN_LEFT);
//        $select->where(
//                new Predicate\PredicateSet(
//                array(
//            new Predicate\IsNull('tbl_user_hits.fld_status'),
//            new Predicate\Operator('tbl_user_hits.fld_status', Predicate\Operator::OPERATOR_EQUAL_TO, 1),
//                ), Predicate\PredicateSet::COMBINED_BY_OR
//                )
//        );

        $genderArray = array(
            'male' => 1,
            'female' => 2,
        );
        $ageFrom = $query['fld_age_from'];
        if (!empty($ageFrom)) {
            $select->where(
                    new Predicate\Operator($this->tableGateway->table . '.fld_age', Predicate\Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $ageFrom)
            );
        }
        $ageTo = $query['fld_age_to'];
        if (!empty($ageTo)) {
            $select->where(
                    new Predicate\Operator($this->tableGateway->table . '.fld_age', Predicate\Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO, $ageTo)
            );
        }
        $gender = $genderArray[$query['fld_gender']];
        if (!empty($gender)) {
            $select->where(
                    array($this->tableGateway->table . '.fld_gender' => $gender)
            );
        }

        $select->where(
                new Predicate\NotIn($this->tableGateway->table . '.user_id', $notInUserIds)
        );
        $select->where(array($this->tableGateway->table . '.fld_is_blocked' => 0));
        $select->where(array($this->tableGateway->table . '.fld_status' => 1));
        $select->order($this->tableGateway->table . '.fld_updated_datetime DESC');
        $select->limit($length);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }

    /**
     * Function verifyVerificationCode() Implemented for 
     * checking fld_verification_code
     * 
     * @param type $param
     * 
     * @return true if subscribed or false as boolean 
     */
    public function verifyVerificationCode($verificationCode = "") {

        $rowset = $this->tableGateway->select(array(
            'fld_verification_code' => $verificationCode));

        $row = $rowset->current();

        return $row->user_id;
    }

    /**
     * Function increaseUserFriendsNotification() Implemented for 
     * increasing user Friends Notification
     * 
     */
    public function increaseUserFriendsNotification($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_friends_notification_count' => new \Zend\Db\Sql\Predicate\Expression('fld_friends_notification_count + 1')), array('user_id' => $fldUserId));
        }
    }

    /**
     * Function resetUserFriendsNotification() Implemented for 
     * resetting user Friends Notification
     * 
     */
    public function resetUserFriendsNotification($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_friends_notification_count' => 0), array('user_id' => $fldUserId));
        }
    }

    /**
     * Function increaseUserMessagesNotification() Implemented for 
     * increasing user Messages Notification
     * 
     */
    public function increaseUserMessagesNotification($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_messages_notification_count' => new \Zend\Db\Sql\Predicate\Expression('fld_messages_notification_count + 1')), array('user_id' => $fldUserId));
        }
    }

    /**
     * Function resetUserMessagesNotification() Implemented for 
     * resetting user Messages Notification
     * 
     */
    public function resetUserMessagesNotification($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_messages_notification_count' => 0), array('user_id' => $fldUserId));
        }
    }

    /**
     * Function getUserLastActiveTime() Implemented for 
     * getting user last active time
     * 
     */
    public function getUserLastActiveTime($fldUserId) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('fld_updated_datetime'));
        $select->where(array('user_id' => $fldUserId));
        $result = $this->tableGateway->selectWith($select);
        return $result->current()->fld_updated_datetime;
    }

    /**
     * Function blockUser() Implemented for 
     * blocking user
     * 
     * @param type $param
     * 
     * @return
     */
    public function blockUser($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_is_blocked' => 1), array('user_id' => $fldUserId));
        }
    }

    /**
     * Function unblockUser() Implemented for 
     * unblocking user
     * 
     * @param type $param
     * 
     * @return 
     */
    public function unblockUser($fldUserId) {
        if (!empty($fldUserId)) {
            $this->tableGateway->update(array('fld_is_blocked' => 0), array('user_id' => $fldUserId));
        }
    }

}
