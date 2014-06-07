<?php

namespace Models\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class Messages extends AbstractTableGateway {

    protected $table = 'threads';

    public function __construct(Adapter $db) {
        $this->adapter = $db;
        $this->initialize();
    }

    public function fetch($id, $userId,$pageFrom) {
        $queryD = "SELECT th.* FROM `threads` th
            INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
            INNER JOIN products pd ON (th.product_id=pd.id)
            LEFT JOIN user usr1 ON (thm.sender_id=usr1.user_id)
            LEFT JOIN user usr2 ON (thm.receiver_id=usr2.user_id)
            WHERE th.id='" . intval($id) . "' AND 
              (
                (thm.sender_id=$userId AND thm.sender_status !=3 )
                OR 
                (thm.receiver_id=$userId AND thm.receiver_status !=3 )
              )
            ";
        $queryExe = $this->adapter->query($queryD);
        $row = $queryExe->execute()->getResource()->fetch(\PDO::FETCH_ASSOC);
//        $id = (int) $id;
//        $rowset = $this->select(array('method_id' => $id));
//        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row thread $id");
        }
        if($pageFrom!=='details') {
            $row['last_message'] = $this->getLastMessage($userId, $id);
        }
        return $row;
    }

    /**
     * Implementation of sendMessage()
     * @return 
     */
    public function sendMessage($productId, $threadId, $message, $senderId, $receiverId, $name, $email, $phone="") {
        $dataTime = time();
        if (empty($threadId)) {
            $queryT = "INSERT INTO threads SET product_id = '" . $productId . "', date = '" . $dataTime . "', mod_date = '" . $dataTime . "'";
            $queryExeT = $this->adapter->query($queryT);
            $queryExeT->execute();
            $threadId = $this->adapter->getDriver()->getLastGeneratedValue();
        }
        if (!empty($threadId)) {
            $queryTM = "INSERT INTO thread_messages SET message = '" . addcslashes($message, "'") . "', thread_id = '" . $threadId . "', sender_id = '" . (int) $senderId . "', receiver_id = '" . $receiverId . "', name = '" . $name . "', email = '" . $email . "', phone = '" . $phone . "', date = '" . $dataTime . "', status = 0";
            $queryExeTM = $this->adapter->query($queryTM);
            $queryExeTM->execute();
            $threadMessageId = $this->adapter->getDriver()->getLastGeneratedValue();
            $queryTU = "UPDATE threads SET mod_date = '" . $dataTime . "' WHERE id=" . $threadId;
            $queryExeTU = $this->adapter->query($queryTU);
            $queryExeTU->execute();
        }
        return ($threadMessageId) ? $threadId : FALSE;
    }

    /**
     * Implementation of getThreads()
     * @return 
     */
    public function getInboxThreads($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage < 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                th.*
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE thm.receiver_id=$userId " . $sql . "
                AND thm.receiver_status=0
                GROUP BY th.id
                ORDER BY th.mod_date DESC
                LIMIT " . $offset . "," . $messagePerPage . "";
        $data = $this->adapter->query($sqlQuery);

        return $results = (array) $data->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Implementation of getThreadsTotalCount()
     * @return 
     */
    public function getInboxThreadsTotalCount($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage == 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                count(DISTINCT th.id) as total_threads 
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE thm.receiver_id=$userId " . $sql . "
                AND thm.receiver_status=0
                ";
        $data = $this->adapter->query($sqlQuery);

        return $results = $data->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Implementation of getSentThreads()
     * @return 
     */
    public function getSentThreads($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage < 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                th.*
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE thm.sender_id=$userId " . $sql . "
                AND thm.sender_status=0
                GROUP BY th.id
                ORDER BY th.mod_date DESC
                LIMIT " . $offset . "," . $messagePerPage . "";
        $data = $this->adapter->query($sqlQuery);

        return $results = (array) $data->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Implementation of getSentThreadsTotalCount()
     * @return 
     */
    public function getSentThreadsTotalCount($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage == 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                count(DISTINCT th.id) as total_threads 
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE thm.sender_id=$userId " . $sql . "
                AND thm.sender_status=0
                ";
        $data = $this->adapter->query($sqlQuery);

        return $results = $data->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Implementation of getSentThreads()
     * @return 
     */
    public function getArchiveThreads($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage < 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                th.*
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE ((thm.sender_id=$userId AND thm.sender_status=2) OR (thm.receiver_id=$userId AND thm.receiver_status=2)) " . $sql . "
                GROUP BY th.id 
                ORDER BY th.mod_date DESC 
                LIMIT " . $offset . "," . $messagePerPage . "";
        $data = $this->adapter->query($sqlQuery);

        return $results = (array) $data->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Implementation of getSentThreadsTotalCount()
     * @return 
     */
    public function getArchiveThreadsTotalCount($userId, $queryArray, $cPage, $messagePerPage) {
        $keyword = $queryArray['keyword'];
        if (!empty($keyword)) {
            $sql = " AND thm.message LIKE '%" . $keyword . "%'";
        }
        $datetime = $queryArray['datetime'];
        $direction = $queryArray['direction'];
        $directionArray = array('prev' => '<', 'next' => '>');
        if (isset($datetime) && $datetime == date('Y-m-d H:i:s', strtotime($datetime)) && isset($direction) && ($direction == 'prev' || $direction == 'next')) {
            $sql .= ' AND th.mod_date ' . $directionArray[$direction] . ' "' . $datetime . '"';
        }
        $offset = ($cPage == 1) ? 0 : ($cPage - 1) * $messagePerPage;
        $sqlQuery = "
                SELECT 
                count(DISTINCT th.id) as total_threads 
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                WHERE ((thm.sender_id=$userId AND thm.sender_status=2) OR (thm.receiver_id=$userId AND thm.receiver_status=2)) " . $sql . "
                ";
        $data = $this->adapter->query($sqlQuery);

        return $results = $data->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Implementation of getLastMessage()
     * @return 
     */
    public function getLastMessage($userId, $threadId, $type) {
        $sql = ($type == 'inbox') ? "thm.receiver_id=$userId" : (($type=='archive') ? (($type == 'sent') ? "thm.sender_id=$userId" : "((thm.sender_id=$userId AND thm.sender_status=2) OR (thm.receiver_id=$userId AND thm.receiver_status=2))"):"((thm.sender_id=$userId) OR (thm.receiver_id=$userId))");
        $sqlQuery = "
                SELECT
                thm.id, thm.message, thm.thread_id, thm.sender_id, thm.receiver_id, thm.name, thm.email, thm.date, thm.status,
                usr1.display_name as sender_name,usr2.display_name as receiver_name,
                usr1.email as sender_email, usr2.email as receiver_email
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                LEFT JOIN user usr1 ON (thm.sender_id=usr1.user_id)
                LEFT JOIN user usr2 ON (thm.receiver_id=usr2.user_id)
                WHERE th.id=$threadId AND " . $sql . "
                ORDER BY thm.date DESC
                LIMIT 1";
        $data = $this->adapter->query($sqlQuery);
        $results = (array) $data->execute()->getResource()->fetch(\PDO::FETCH_ASSOC);
        $results['sender_name']=(string) $results['sender_name'];
        $results['sender_email']=(string) $results['sender_email'];
        return $results;
    }

    /**
     * Implementation of updateMessageStatus()
     * for updating status of thread as read, unread, archive. delete
     * used in messages controller
     * @return 
     */
    public function updateMessageStatus($userId, $threadIds, $status, $type) {
        if ($type != 'archive') {
            $field = ($type == 'sent') ? 'sender_id' : 'receiver_id';
            $userMessageStatus = ($type == 'sent') ? 'sender_status' : 'receiver_status';
            $statusArray = array('0' => 'status', 1 => 'status', 2 => $userMessageStatus, 3 => $userMessageStatus);
            $sqlQuery = "
            UPDATE thread_messages thm
            INNER JOIN threads th ON (thm.thread_id=th.id)
            INNER JOIN products pd ON (th.product_id=pd.id)
            SET thm." . $statusArray[$status] . "=$status
            WHERE thm." . $field . "=$userId AND thm." . $statusArray[$status] . " !=$status AND th.id IN (" . implode(",", $threadIds) . ")
             ";
        } else {
            $sqlQuery = "
            UPDATE thread_messages thm
            INNER JOIN threads th ON (thm.thread_id=th.id)
            INNER JOIN products pd ON (th.product_id=pd.id)
            SET thm.sender_status=
                    CASE
                    WHEN sender_id=$userId THEN 3
                    ELSE sender_status
                    END,
                thm.receiver_status=
                    CASE
                    WHEN receiver_id=$userId THEN 3
                    ELSE receiver_status
                    END

            WHERE (
                    (thm.sender_id=$userId AND thm.sender_status =2 )
                    OR 
                    (thm.receiver_id=$userId AND thm.receiver_status =2 )
                  )
            AND th.id IN (" . implode(",", $threadIds) . ")
             ";
        }

        $updateQ = $this->adapter->query($sqlQuery);

        $isUpdate = $updateQ->execute();
        $rowsAffected = $isUpdate->count();
        return ($rowsAffected >= 0) ? TRUE : FALSE;
    }

    /**
     * Implementation of getAllThreadMessages()
     * @return 
     */
    public function getAllThreadMessages($userId, $threadId) {
        $sqlQuery = "
                SELECT 
                thm.id, thm.message, thm.thread_id, thm.sender_id, thm.receiver_id, thm.name, thm.email, thm.date, thm.status,
                usr1.display_name as sender_name,usr2.display_name as receiver_name
                FROM threads th
                INNER JOIN thread_messages thm ON (th.id=thm.thread_id)
                INNER JOIN products pd ON (th.product_id=pd.id)
                LEFT JOIN user usr1 ON (thm.sender_id=usr1.user_id)
                LEFT JOIN user usr2 ON (thm.receiver_id=usr2.user_id)
                WHERE th.id=$threadId AND 
                  (
                    (thm.sender_id=$userId)
                    OR 
                    (thm.receiver_id=$userId)
                  )
                ORDER BY thm.date
                ";
        $data = $this->adapter->query($sqlQuery);

        return $results = (array) $data->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

}

