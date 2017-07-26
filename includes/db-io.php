<?php
/**
 * @file db-io.php
 * File contains all the database IO wrapper functions that are used to ease communication with the `tktpass` database.
 *
 * @defgroup io Database IO
 * @brief All functions for communicating with the `tktpass` database.
 * @{
 */
    require_once 'db-setup.php';

    /* ==================================================================================== *
       Events Table
     * ==================================================================================== */

    /**
    * @defgroup io-events Event Table IO
    * @brief All functions for communicating with the `events` table of the `tktpass` database.
    * @{
    */

    /**
    * Inserts a new row in the `events` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param mixed[] $data New row data to be inserted into the table. Required.
    *   ```text
    *   $data = [
    *     'name'        => (string)  Name of the new event. Required.
    *     'host'        => (string)  Presentable name for the group/company etc organising the event. Required.
    *     'start'       => (string)  String datetime represetning the start date/time of the event, in the format d/m/y H:i. Required.
    *     'venue'       => (string)  The name of the venue where the event will be taking place.
    *     'address_1'   => (string)  Line 1 of the venue address. Required.
    *     'address_2'   => (string)  Line 2 of the venue address, null by default.
    *     'city'        => (string)  City of the venue address.
    *     'postcode'    => (string)  Postcode of the venue address.
    *     'end'         => (string)  String datetime represetning the end date/time of the event, in the format d/m/y H:i. If no end time is provided, an event is assumed to have ended 4 hours after the start.
    *     'description' => (string)  HTML for the description of the event. Required.
    *     'image'       => (string)  URL to an external image to use for event's featured image.
    *     'private'     => (boolean) Whether this event is private or not. Default is false.
    *     'fb_id'       => (string)  The Facebook ID of the relevent event on Facebook.com.
    *     'user_id'     => (int)     The ID of the user creating this event. Required.
    *   ]
    *   ```
    *
    * @return mixed[] Returns the newly inserted row on success (including its assigned ID in the table) or on error it returns data describing the error.
    *   
    *   On success
    *   ```text
    *     $row = [
    *       'id'          => (int)     The row ID of the newly inserted event.
    *       'name'        => (string)  Name of the new event.
    *       'host'        => (string)  Presentable name for the group/company etc organising the event.
    *       'start'       => (string)  String datetime represetning the start date/time of the event, in the format Y-m-d H:i.
    *       'venue'       => (string)  The name of the venue where the event will be taking place.
    *       'address_1'   => (string)  Line 1 of the venue address.
    *       'address_2'   => (string)  Line 2 of the venue address, null by default.
    *       'city'        => (string)  City of the venue address.
    *       'postcode'    => (string)  Postcode of the venue address.
    *       'end'         => (string)  String datetime represetning the end date/time of the event, in the format Y-m-d H:i.
    *       'description' => (string)  HTML for the description of the event.
    *       'image'       => (string)  URL to an external image to use for event's featured image.
    *       'private'     => (boolean) Whether this event is private or not.
    *       'fb_id'       => (string)  The Facebook ID of the relevent event on Facebook.com.
    *       'user_id'     => (int)     The ID of the user creating this event.
    *       'created'     => (string)   Datetime of when this event was inserted into the database, in the format Y-m-d H:i.
    *     ]
    *   ```
    *   On error
    *   ```text
    *     $error = [
    *       'err'    => (string) Description of the error that occurred.
    *       'status' => (int)    HTTP status code to return the error with.
    *     ]
    *   ```
    */
    function insert_event($data){
        if(!isset($data)){
            return array("err"=>"Missing $data argument: new row data to be inserted","status"=>400);
        }
        global $db;
        $required_keys = array("name","host","start","address_1","description","user_id");
        foreach ($required_keys as $required_key) {
          if(!isset($data[$required_key])){
            return array("err"=>"Missing required: ".$required_key,"status"=>400);
          }
        }
        $keys = array_keys($data);
        $allowed_keys = array("id", "name", "host", "start", "venue", "address_1", "address_2", "city", "postcode", "end", "description", "image", "private", "fb_id", "user_id", "created");
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`, `',$keys).'`';
        $placeholder = rtrim(str_repeat('?,', count($keys)), ",");
        try{
            $stmt = $db->prepare("INSERT INTO `events` ($fields) VALUES ($placeholder)");
            if($stmt->execute(array_values($data))){
                $id = $db->lastInsertId();
                $res = $db->query("SELECT * FROM `events` WHERE id = ".$id);
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Checks whether a row with the given ID exists in the `events` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID to check for in the table.
    *
    * @return boolean Whether a row with the given ID does exists in the `events` table or not.
    */
    function event_exists($id){
        global $db;
        $stmt = $db->prepare('SELECT COUNT(id) FROM events WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)($stmt->fetchColumn());
    }

    /**
    * Gets the row with the given ID from the `events` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to fetch from the `events` table.
    *
    * @return mixed[] If a row with the given ID does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function get_event($id){
        global $db;
        $stmt = $db->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Updates a row in the `events` table with the given data.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to update in the `events` table.
    * @param mixed[] $data An array with key-value pairs corresponding to the columns which need updating.
    *
    * @return mixed[] On success, returns the newly updated row (including all columns in the table), or on error it returns data describing the error (`err` and `status` keys).
    */
    function update_event($id, $data){
        global $db;
        $keys = array_keys($data);
        $allowed_keys = array("id", "name", "host", "start", "address_1", "address_2", "city", "postcode", "end", "description", "image", "private", "fb_id", "user_id", "created");
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`= ?, `',$keys).'` = ?';
        try{
            $stmt = $db->prepare("UPDATE `events` SET $fields WHERE `id` = ?");
            if($stmt->execute(array_merge(array_values($data),array($id)))){
                $stmt = $db->prepare("SELECT * FROM `events` WHERE id = ?");
                if($stmt->execute($data["id"] ? array($data["id"]) : array($id)))
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
            else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Deletes the row with the given ID from the `events` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to delete from the `events` table.
    *
    * @return mixed[] On success, returns a simple array with one key (`success`) and gives it the value `true`. On error it returns data describing the error (`err` and `status` keys).
    */
    function delete_event($id){
        global $db;
        $stmt = $db->prepare('DELETE FROM events WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                return array("success"=>true);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all events that start between the specified dates.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $from Date string from which to start looking for events. Default is the first day of the current month.
    * @param string $to Date string giving the latest wanted start date. Default is the last day of next month.
    *
    * @return array[] Returns an array of arrays, where each item in the array is the row data for one of the fetched events. The events are sorted with ascending start times. Usual error array is returned on error.
    */
    function get_events($from,$to){
        global $db;
        if(!isset($from) || !$from){
            //$fdolm = (new DateTime())->setTimestamp(mktime(0, 0, 0, date('n')-1, 1, date('Y')))->format("Y-m-d");
            $fdotm = date('Y-m-01');//;
            $from = $fdotm;
        }
        if(!isset($to) || !$to){
            //$ldotm = date('Y-m-t');
            $ldonm = (new DateTime())->setTimestamp(mktime(0, 0, 0, date('n')+2, 0, date('Y')))->format("Y-m-d");
            $to = $ldonm;
        }
        if(strtotime($from) > strtotime($to)){
            $tmp = $from;
            $from = $to;
            $to = $tmp;
            $tmp = null;
        }
        try{
            $res = $db->query("SELECT * FROM events WHERE DATE_FORMAT(start, '%Y-%m-%d') BETWEEN '$from' AND '$to' ORDER BY start ASC");
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all rows from the `events` table whose ID exists in the given array of IDs.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int[] $ids An array of event IDs to fetch from the `events` table.
    *
    * @return mixed[] Returns an array of arrays, where each item in the array is the row data for one of the fetched events. Usual error array is returned on error.
    */
    function get_events_by_id($ids){
        global $db;
        $idsStr = implode(',', $ids);
        try{
            $res = $db->query("SELECT * FROM events WHERE id in ($idsStr)");
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all rows from the `events` table that correspond to events created by the user with the given user ID, and were created since the given date.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $userId The ID of the user whose events are to be fetched. Default is the ID of the current logged in user.
    * @param string The earliest date from which events should be fetched, defaults to start of the month 6 months in the past.
    *
    * @return mixed[] Returns an array of arrays, where each item in the array is the row data for one of the fetched events. Usual error array is returned on error.
    */
    function get_user_events($userId=null,$from=null){
        global $db;
        if(isset($userId) && $userId){
            if(!user_exists($userId))
                return array("err"=>"User id ".$userId." not recognised","status"=>404);
        } else {
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $userId = $_SESSION['user']['id'];
        }
        if(!isset($from) || !$from){
            $sm = (new DateTime())->setTimestamp(mktime(0, 0, 0, date('n')-6, 1, date('Y')))->format("Y-m-d");
            //$fdolm = (new DateTime())->setTimestamp(mktime(0, 0, 0, date('n')-1, 1, date('Y')))->format("Y-m-d");
            //$fdotm = date('Y-m-01');
            $from = $sm;
        }
        try{
            $res = $db->query("SELECT * FROM `events` WHERE `user_id` = $userId AND `start` > '$from' ORDER BY `start` DESC");
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all rows from the `events` table that correspond to events created by the user with the given user ID, and that have not yet finished or started.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $userId The ID of the user whose events are to be fetched. Default is the ID of the current logged in user.
    *
    * @return mixed[] Returns an array of arrays, where each item in the array is the row data for one of the fetched events. Usual error array is returned on error.
    */
    function get_user_upcoming_events($userId=null){
        global $db;
        if(isset($userId) && $userId){
            if(!user_exists($userId))
                return array("err"=>"User id ".$userId." not recognised","status"=>404);
        } else {
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $userId = $_SESSION['user']['id'];
        }
        try{
            $query = <<<EOF
SELECT * FROM events
WHERE user_id = $userId AND (
    (end IS NOT NULL AND end > NOW())
    OR
    (end IS NULL AND start > DATE_SUB(NOW(), INTERVAL 4 HOUR))
) ORDER BY start ASC
EOF;
            $res = $db->query($query);
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }
    /** @}*/

    /* ==================================================================================== *
       Users Table
     * ==================================================================================== */

    /**
    * @defgroup io-users Users Table IO
    * @brief All functions for communicating with the `users` table of the `tktpass` database.
    * @{
    */

    /**
    * Inserts a new row in the `users` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param mixed[] $data New row data to be inserted into the table. Required.
    *   ```text
    *   $data = [
    *     'first_name'          => (string) 
    *     'last_name'           => (string) 
    *     'email'               => (string) 
    *     'hash'                => (string)  Salted hash of this user's password.
    *     'plan'                => (string) 
    *     'referral'            => (string)  User ID of the user that referred this user, if applicable.
    *     'customer_id'         => (string)  Stripe customer ID.
    *     'mobile'              => (string) 
    *     'fb_id'               => (string)  Tktpass-app-scoped Facebook user ID.
    *     'fb_access_token'     => (string)  OAuth access token to act communicate with Facebook on this users behalf.
    *     'fb_access_expires'   => (string)  Y-m-d H:i:s datetime string detailing when the access token will expire.
    *     'birthday'            => (string)  Y-m-d date string
    *     'gender'              => (boolean) 0 = Male, 1 = Female
    *     'city'                => (string) 
    *     'country'             => (string)  Full country name
    *     'mailing_list'        => (boolean) Whether this user is in the mailing list or not (default is true, false if opted out)
    *     'account_id'          => (string)  Stripe account ID
    *     'account_secret'      => (string)  Stripe account secret key
    *     'account_publishable' => (string)  Stripe account publishable key
    *     'verified'            => (boolean) Whether this user has been verified (gives hosting privileges) or not, default is false.
    *   ]
    *   ```
    *
    * @return mixed[] Returns the newly inserted row on success (including its assigned ID in the table) or on error it returns data describing the error.
    *   
    *   On success
    *   ```text
    *     $row = [
    *       'id'                  => (int) 
    *       'first_name'          => (string) 
    *       'last_name'           => (string) 
    *       'email'               => (string) 
    *       'hash'                => (string)  Salted hash of this user's password.
    *       'joined'              => (string)  Datetime this user account was created, Y-m-d H:i:s format.
    *       'plan'                => (string) 
    *       'referral'            => (string)  User ID of the user that referred this user, if applicable.
    *       'customer_id'         => (string)  Stripe customer ID.
    *       'mobile'              => (string) 
    *       'fb_id'               => (string)  Tktpass-app-scoped Facebook user ID.
    *       'fb_access_token'     => (string)  OAuth access token to act communicate with Facebook on this users behalf.
    *       'fb_access_expires'   => (string)  Y-m-d H:i:s datetime string detailing when the access token will expire.
    *       'birthday'            => (string)  Y-m-d date string
    *       'gender'              => (boolean) 0 = Male, 1 = Female
    *       'city'                => (string) 
    *       'country'             => (string)  Full country name
    *       'mailing_list'        => (boolean) Whether this user is in the mailing list or not (default is true, false if opted out)
    *       'last_active'         => (string)  Y-m-d H:i:s datetime string detailing the last time this user was active.
    *       'account_id'          => (string)  Stripe account ID
    *       'account_secret'      => (string)  Stripe account secret key
    *       'account_publishable' => (string)  Stripe account publishable key
    *       'verified'            => (boolean) Whether this user has been verified (gives hosting privileges) or not, default is false.
    *     ]
    *   ```
    *   On error
    *   ```text
    *     $error = [
    *       'err'    => (string) Description of the error that occurred.
    *       'status' => (int)    HTTP status code to return the error with.
    *     ]
    *   ```
    */
    function insert_user($data){
        global $db;
        $allowed_keys = array("id", "first_name", "last_name", "email", "hash", "joined", "plan", "referral", "customer_id", "mobile", "fb_id", "fb_access_token", "fb_access_expires", "birthday", "gender", "city", "country", "mailing_list", "last_active", "account_id", "account_secret", "account_publishable", "verified");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`, `',$keys).'`';
        $placeholder = rtrim(str_repeat('?,', count($keys)), ",");
        try{
            $stmt = $db->prepare("INSERT INTO `users` ($fields) VALUES ($placeholder)");
            if($stmt->execute(array_values($data))){
                $id = $db->lastInsertId();
                $res = $db->query("SELECT * FROM `users` WHERE id = ".$id);
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Checks whether a row with the given ID exists in the `users` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID to check for in the table.
    * @param boolean $fb If `true` this indicates the passed ID is an <a href="https://developers.facebook.com/docs/apps/for-business" target="_blank">app-scoped Facebook ID</a> (rather than the otherwise assumed `tktpass.users` ID) and so will look in in the `fb_id` column in the `users` table instead of the `id` column.
    *
    * @return boolean|string If a `tktpass.users` ID was passed, returns a boolean indicating whether a row with the given ID does exists in the `users` table or not. If an <a href="https://developers.facebook.com/docs/apps/for-business" target="_blank">app-scoped Facebook ID</a> was passed instead, this function returns the corresponding user's `tktpass.users` table `id` (a `truth`y value) and `false` if no row with that `fb_id` exists.
    */
    function user_exists($id, $fb=false){
        global $db;
        $stmt = $db->prepare('SELECT '.($fb?'id':'COUNT(id)').' FROM users WHERE '.($fb?'fb_id':'id').' = ? LIMIT 1');
        $stmt->bindParam(1, intval($id), PDO::PARAM_INT);
        if($stmt->execute()){
            if($fb){
                if($res = $stmt->fetch(PDO::FETCH_ASSOC))
                    return $res["id"];
                else return false;
            }
            else
                return (bool)($stmt->fetchColumn());
        } else
            return array("err"=>$stmt->errorInfo()[2],"status"=>500);
    }

    /**
    * Gets the row with the given ID from the `users` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to fetch from the `users` table.
    *
    * @return mixed[] If a row with the given ID does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function get_user($id=null){
        global $db;
        if(!isset($id) || !$id){
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $id = $_SESSION['user']['id'];
        }
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Utility function to check whether the user with the given ID has an email address in the `email` column of the `users` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the user whose `email` is to be checked.
    *
    * @return boolean Whether the given user has an `email` in the `users` table or not.
    */
    function user_has_email($id){
        global $db;
        if(!isset($id) || !$id){
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            if(isset($_SESSION['email']))
                return (bool)($_SESSION['email']);
            else
                $id = $_SESSION['user']['id'];
        }
        $stmt = $db->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, intval($id), PDO::PARAM_INT);
        if($stmt->execute())
            return (bool)($stmt->fetchColumn());
        else
            return array("err"=>$stmt->errorInfo()[2],"status"=>500);
    }

    /**
    * If one exists, gets the user from the `users` table whose `email` matches the one given.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $email The email to search for in the `users` table.
    *
    * @return mixed[] If a row with the given email does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function get_user_by_email($email){
        global $db;
        if(!isset($email) || !$email)
            return array("err"=>"User email not provided","status"=>500);
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets the <a href="https://stripe.com/docs/api/php#customer_object-id" target="_blank">Stripe `customer_id`</a> of the user with the given ID.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The `tktpass.users` ID of the user.
    *
    * @return string|boolean If the relevent user has a Stripe customer ID, it is returned, `false` if it does not have one and the usual error array if some other error occurs.
    */
    function get_customer_id($id){
        global $db;
        if(!$id){
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $id = $_SESSION['user']['id'];
        }
        $stmt = $db->prepare('SELECT customer_id FROM users WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res["customer_id"] ? $res["customer_id"] : false;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Updates a row in the `users` table with the given data.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to update in the `users` table.
    * @param mixed[] $data An array with key-value pairs corresponding to the columns which need updating.
    *
    * @return mixed[] On success, returns the newly updated row (including all columns in the table), or on error it returns data describing the error (`err` and `status` keys).
    */
    function update_user($id, $data){
        global $db;
        $allowed_keys = array("id", "first_name", "last_name", "email", "hash", "joined", "plan", "referral", "customer_id", "mobile", "fb_id", "fb_access_token", "fb_access_expires", "birthday", "gender", "city", "country", "mailing_list", "last_active", "account_id", "account_secret", "account_publishable", "verified");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`= ?, `',$keys).'` = ?';
        try{
            $stmt = $db->prepare("UPDATE `users` SET $fields WHERE `id` = ?");
            if($stmt->execute(array_merge(array_values($data),array($id)))){
                $stmt = $db->prepare("SELECT * FROM `users` WHERE id = ?");
                $stmt->execute($data["id"] ? $data["id"] : array($id));
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Deletes the row with the given ID from the `users` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to delete from the `users` table.
    *
    * @return mixed[] On success, returns a simple array with one key (`success`) and gives it the value `true`. On error it returns data describing the error (`err` and `status` keys).
    */
    function delete_user($id){
        global $db;
        $stmt = $db->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                return array("success"=>true);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }
    /** @}*/

    /* ==================================================================================== *
       Ticket Types Table
     * ==================================================================================== */

    /**
    * @defgroup io-types Ticket Types Table IO
    * @brief All functions for communicating with the `event_ticket_types` table of the `tktpass` database.
    * @{
    */

    /**
    * Inserts a new row in the `event_ticket_types` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param mixed[] $data New row data to be inserted into the table. Required.
    *   ```text
    *   $data = [
    *    'event_id' => (int) The ID of the event this corresponds to. Required.
    *    'type' => (int) 0 = Free, 1 = Paid, 2 = Donation. Required.
    *    'name' => (string) Name of this ticket type. Required.
    *    'price' => (int) Price in pence. Required.
    *    'quantity' => (int) Maximum quantity to sell of this ticket type. Required.
    *   ]
    *   ```
    *
    * @return mixed[] Returns the newly inserted row on success (including its assigned ID in the table) or on error it returns data describing the error.
    *   
    *   On success
    *   ```text
    *     $row = [
    *      'id' => (string) The unique row ID for this ticket type in the event_ticket_types table
    *      'event_id' => (int) The ID of the event this corresponds to
    *      'type' => (int) 0 = Free, 1 = Paid, 2 = Donation
    *      'name' => (string) Name of this ticket type
    *      'price' => (int) Price in pence
    *      'quantity' => (int) Maximum quantity to sell of this ticket type
    *      'added' => (string) Datetime string in Y-m-d H:i:s format
    *     ]
    *   ```
    *   On error
    *   ```text
    *     $error = [
    *       'err'    => (string) Description of the error that occurred.
    *       'status' => (int)    HTTP status code to return the error with.
    *     ]
    *   ```
    */
    function insert_ticket_type($data){
        global $db;
        $allowed_keys = array("id", "event_id", "type", "name", "price", "quantity","added");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        if(in_array("id", $keys)){
          return array("err"=>"Cannot set ticket type id on insert","status"=>400);
        }
        $fields = '`'.implode('`, `',$keys).'`';
        $placeholder = rtrim(str_repeat('?,', count($keys)), ",");
        try{
            $stmt = $db->prepare("INSERT INTO `event_ticket_types` ($fields) VALUES ($placeholder)");
            if($stmt->execute(array_values($data))){
                $id = $db->lastInsertId();
                $res = $db->query("SELECT * FROM `event_ticket_types` WHERE id = ".$id);
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Checks whether a row with the given ID exists in the `event_ticket_types` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID to check for in the table.
    *
    * @return boolean Whether a row with the given ID does exists in the `event_ticket_types` table or not.
    */
    function ticket_type_exists($id){
        global $db;
        $stmt = $db->prepare('SELECT id FROM event_ticket_types WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, intval($id), PDO::PARAM_INT);
        if($stmt->execute())
            return (bool)($stmt->fetchColumn());
        else
            return array("err"=>$stmt->errorInfo()[2],"status"=>500);
    }

    /**
    * Gets the row with the given ID from the `event_ticket_types` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to fetch from the `event_ticket_types` table.
    *
    * @return mixed[] If a row with the given ID does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function get_ticket_type($id){
        global $db;
        $stmt = $db->prepare('SELECT * FROM event_ticket_types WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all ticket types of the event with the given event ID.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $event_id The event ID of the event whose ticket types should be fetched.
    *
    * @return array[]|mixed[] Returns an array of arrays, where each item in the array is the row data for one of the fetched ticket types. Usual error array is returned on error.
    */
    function get_event_ticket_types($event_id){
        global $db;
        $stmt = $db->prepare('SELECT * FROM event_ticket_types WHERE event_id = ?');
        $stmt->bindParam(1, $event_id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $res;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /*
    * Gets the IDs of all ticket types of the event with the given event ID.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $event_id The event ID of the event whose ticket type IDs should be fetched.
    *
    * @return int[] Returns an array containing all the ticket type IDs for this event. Usual error array is returned on error.
    *
    function get_event_ticket_type_ids($event_id){
        global $db;
        $stmt = $db->prepare('SELECT id FROM event_ticket_types WHERE event_id = ?');
        $stmt->bindParam(1, $event_id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array_map(function($arr){return $arr["id"];}, $res);
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }*/

    /**
    * Updates a row in the `event_ticket_types` table with the given data.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID of the row to update in the `event_ticket_types` table.
    * @param mixed[] $data An array with key-value pairs corresponding to the columns which need updating.
    *
    * @return mixed[] On success, returns the newly updated row (including all columns in the table), or on error it returns data describing the error (`err` and `status` keys).
    */
    function update_ticket_type($id, $data){
        global $db;
        if(!ticket_type_exists($id))
            return array("err"=>"Event ticket type ID ".$id." not recognised","status"=>404);
        $allowed_keys = array("id", "event_id", "type", "name", "price", "quantity","added");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`= ?, `',$keys).'` = ?';
        try{
            $stmt = $db->prepare("UPDATE `event_ticket_types` SET $fields WHERE `id` = ?");
            if($stmt->execute(array_merge(array_values($data),array($id)))){
                $stmt = $db->prepare("SELECT * FROM `event_ticket_types` WHERE id = ?");
                if($stmt->execute($data["id"] ? array($data["id"]) :  array($id)))
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
            else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /*
    * Adds `$num` to the `sold` counter of the ticket type with the given ID.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID of the ticket type whose sold counter need incrementing.
    * @param int $num The amount with which to increment the counter by. Defaults to 1.
    *
    * @return mixed[] Returns the full updated row data.
    *
    function add_to_ticket_type_sold($id, $num=1){
        global $db;
        if(!ticket_type_exists($id))
            return array("err"=>"Event ticket type ID ".$id." not recognised","status"=>404);
        try{
            $res = $db->query("UPDATE `event_ticket_types` SET `sold` = `sold`+".intval($num)." WHERE `id` = ".intval($id));
            if($res){
                $res = $db->query("SELECT * FROM `event_ticket_types` WHERE id = ".intval($id)." LIMIT 1");
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            }
            else return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }*/

    /**
    * Calculates the total number of sold tickets for the given ticket type ID.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID of the ticket type of which to calculate the total sold.
    *
    * @bug Shouldn't count resold or passed tickets towards the sold count
    *
    * @return int|mixed[] Returns the total number of sold tickets for this ticket type ID, or usual error array on error.
    */
    function get_ticket_type_sold($id){
        global $db;
        if(!ticket_type_exists($id))
            return array("err"=>"Event ticket type ID ".$id." not recognised","status"=>404);
        try{
            $res = $db->query("SELECT count(id) AS sold FROM `tickets` WHERE `event_ticket_type_id` = ".$id);
            if($res)
                return $res->fetch(PDO::FETCH_ASSOC)["sold"];
            else return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Deletes the row with the given ID from the `event_ticket_types` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to delete from the `event_ticket_types` table.
    *
    * @return mixed[] On success, returns a simple array with one key (`success`) and gives it the value `true`. On error it returns data describing the error (`err` and `status` keys).
    */
    function delete_ticket_type($id){
        global $db;
        $stmt = $db->prepare('DELETE FROM event_ticket_types WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute())
                return array("success"=>true);
            else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Repace all ticket types associated with this event ID, with those ticket types in the `$tickets` array.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the event ID to update types on.
    * @param array $tickets An array of arrays where each item in the array is ticket type row data to update/replace the existing rows with.
    *
    * @return array[] On success, returns an array[] containing the newly inserted/updated rows.
    */
    function put_event_ticket_types($event_id,$tickets){
        global $db;
        $current = get_event_ticket_types($event_id);
        if(!is_array($current) || $current["err"])
            return array("err"=>($current?$current["err"]:"Error unknown"),"status"=>($current?$current["status"]:500));
        $error = false;
        $db->beginTransaction();
        if(count($current) === 0){
            //No tickets in db, just insert all these
            foreach($tickets as $ticket){
                $result = insert_ticket_type(array_merge($ticket,array("event_id"=>$event_id)));
                if(!$result || $result["err"]){
                    $error = $result["err"];
                    break;
                }
            }
        } else {
            //Tickets exist in db already so find out what needs doing
            function array_search_by_key($array,$key,$val){
                foreach($array as $i=>$array_item) {
                    if ($array_item[$key] === $val)
                        return $i;
                }
                return -1;
            }
            foreach($current as $i => $current_ticket){
                $index = array_search_by_key($tickets,"id",$current_ticket["id"]);
                if($index > -1){
                    //Current ticket is present in tickets, see if it needs updating
                    $diff = false;
                    foreach($current_ticket as $key => $value){
                        if($tickets[$index][$key] !== $value){
                            $diff = true;
                            break;
                        }
                    }
                    if($diff){
                        $result = update_ticket_type($current_ticket["id"],$tickets[$index]);
                        if(!$result || $result["err"]){
                            $error = $result["err"];
                            break;
                        }
                    }
                } else {
                    //Current ticket is not in tickets, needs deleting
                    $result = delete_ticket_type($current_ticket["id"]);
                    if(!$result || $result["err"]){
                        $error = $result["err"];
                        break;
                    }
                }
            }
            //Check for error after updates/deletes, if not do inserts
            if(!$error){
                $insert_tickets = array();
                foreach($tickets as $i => $ticket){
                    if(!isset($ticket['id']))
                        array_push($insert_tickets, $ticket);
                }
                if(count($insert_tickets) > 0){
                    foreach($insert_tickets as $insert_ticket){
                        $result = insert_ticket_type(array_merge($insert_ticket,array("event_id"=>$event_id)));
                        if(!$result || $result["err"]){
                            $error = $result["err"];
                            break;
                        }
                    }
                }
            }
        }
        if($error) {
            $db->rollBack();
            return array("err"=>$error,"status"=>500);
        } else {
            $db->commit();
            return get_event_ticket_types($event_id);
        }
    }
    /** @}*/

    /* ==================================================================================== *
       Tickets Table
     * ==================================================================================== */

    /**
    * @defgroup io-tickets Tickets Table IO
    * @brief All functions for communicating with the `tickets` table of the `tktpass` database.
    * @{
    */

    /**
    * Utyility fuction for constructing a pseudo-random alphanumeric string (a *token*).
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $length The number of characters in the token. Defaults to 8.
    *
    * @return string The pseudo-random alphanumeric token.
    */
    function getToken($length=8){
        $token = "";
        $codeAlphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";
        $max = strlen($codeAlphabet)-1;
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max)];
        }
        return $token;
    }

    /**
    * Inserts a new row in the `tickets` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param mixed[] $data New row data to be inserted into the table. Required.
    *   ```text
    *   $data = [
    *     'event_ticket_type_id'    => (int)     The ticket type ID of this ticket.
    *     'user_id'                 => (int)     The user this ticket belongs to.
    *     'charge_id'               => (string)  The Stripe charge_id from the payment of this ticket.
    *     'time'                    => (string)  Datetime ticket was purchased in Y-m-d H:i:s format, usually left to defualt to the current time.
    *     'bought_ticket'           => (string)  If this ticket was bought from another person reselling, this is the ticket ID of the original ticket.
    *     'transferred_from_ticket' => (string)  If this ticket was passed to this user, this is the original ticket ID.
    *   ]
    *   ```
    *
    * @return mixed[] Returns the newly inserted row on success (including its assigned ID in the table) or on error it returns data describing the error.
    *   
    *   On success
    *   ```text
    *     $row = [
    *       'id'                      => (string)  The ID of this ticket.
    *       'event_ticket_type_id'    => (int)     The ticket type ID of this ticket.
    *       'user_id'                 => (int)     The user this ticket belongs to.
    *       'charge_id'               => (string)  The Stripe charge_id from the payment of this ticket.
    *       'time'                    => (string)  Datetime ticket was purchased in Y-m-d H:i:s format.
    *       'bought_ticket'           => (string)  If this ticket was bought from another person reselling, this is the ticket ID of the original ticket.
    *       'selling_time'            => (string)  Datetime ticket was placed up for resale, Y-m-d H:i:s format.
    *       'selling_price'           => (int)     The price in pence that this ticket is up for resale (or has sold) for.
    *       'sold_ticket'             => (string)  If this ticket successfully resells, this is the ticket ID of the new ticket.
    *       'transferred_from_ticket' => (string)  If this ticket was passed to this user, this is the original ticket ID.
    *       'transferring_time'       => (string)  If the user requests this ticket is transfered to another user, this is the datetime the ticket transfer originally requested, in Y-m-d H:i:s format.
    *       'transferring_to'         => (string)  This is the ID of the user the owner has requested the transfer to.
    *       'transfer_price'          => (int)     The user-specified price in pence to charge the new user in order to accept the transfer.
    *       'transfer_time'           => (string)  The datetime the other user accepted the transfer, Y-m-d H:i:s format.
    *       'transferred_ticket'      => (string)  The ID of the newly generated ticket after a transfer is accepted.
    *       'used'                    => (boolean) Whether this ticket has been checked-in at the event.
    *     ]
    *   ```
    *   On error
    *   ```text
    *     $error = [
    *       'err'    => (string) Description of the error that occurred.
    *       'status' => (int)    HTTP status code to return the error with.
    *     ]
    *   ```
    * @see <a href="https://stripe.com/docs/api/php#charge_object-id" target="_blank">Stripe `charge_id`</a>
    *
    * @todo Data redundancy here (more of a database design problem): `transferred_ticket` on the original and `transferred_from_ticket` on the new draws the same connection; `sold_ticket` on the original and `bought_ticket` on the new draws the same connection; `transfer_time` on the original and `time` on the new will hold the same data. Consider improving, perhaps best with booleans on each ticket row and another table for relationships between tickets.
    */
    function insert_ticket($data){
        global $db;
        $allowed_keys = array("id","event_ticket_type_id","user_id","charge_id","time","selling_time","selling_price","sold_ticket","transferred_from_ticket","transferring_to","transfer_price","transfer_time","transferred_ticket","used");
        if(!isset($data["id"]))
          $data["id"] = getToken(8);
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`, `',$keys).'`';
        $placeholder = rtrim(str_repeat('?,', count($keys)), ",");
        try{
            $stmt = $db->prepare("INSERT INTO `tickets` ($fields) VALUES ($placeholder)");
            if($stmt->execute(array_values($data))){
                $res = $db->query("SELECT * FROM `tickets` WHERE id = '".$data["id"]."'");
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Checks whether a row with the given ID exists in the `tickets` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID to check for in the table.
    *
    * @return boolean Whether a row with the given ID does exists in the `tickets` table or not.
    */
    function ticket_exists($id){
        global $db;
        $stmt = $db->prepare('SELECT COUNT(id) FROM tickets WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id);
        if($stmt->execute())
            return (bool)($stmt->fetchColumn());
        else
            array("err"=>$stmt->errorInfo()[2],"status"=>500);
    }

    /**
    * Gets the row with the given ID from the `tickets` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to fetch from the `tickets` table.
    *
    * @return mixed[] If a row with the given ID does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function get_ticket($id){
        global $db;
        if(!isset($id) || !$id){
            return array("err"=>"No ticket ID passed","status"=>500);
        }
        $stmt = $db->prepare('SELECT * FROM tickets WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id);
        try{
            if($stmt->execute())
                return $stmt->fetch(PDO::FETCH_ASSOC);
            else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all rows from the `tickets` table whose ID exists in the given array/list/argument list of IDs.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string|string[] $ids,... Three options:
    *   @li *string[]*    an array of ticket IDs
    *   @li *string*      a single comma-separated list of IDs
    *   @li  *string,...* an arguments list of individual IDs.
    *
    *   These IDs will be fetched from the `tickets` table.
    *
    * @return mixed[] Returns an array of arrays, where each item in the array is the row data for one of the fetched tickets. Usual error array is returned on error.
    */
    function get_tickets($ids){
        if ( func_num_args() > 1 )
            $ids = array_unique(func_get_args());
        global $db;
        if(!isset($ids) || !$ids || empty($ids)){
            return array("err"=>"No ticket IDs passed","status"=>500);
        }
        if(!is_array($ids)){
            $ids = explode(',',$ids);
        }
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare('SELECT * FROM tickets WHERE id in ('.$inQuery.')');
        foreach ($ids as $i => $id)
            $stmt->bindValue(($i+1), $id);
        try{
            if($stmt->execute())
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all tickets purchased by this user that have not been resold or transferred and are for events at most 3 months in the past.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $userId The ID of the user whose tickets are to be fetched. Default is the ID of the current logged in user.
    *
    * @return array[] Returns an array of arrays where each item in the array is the information regarding a set of purchased tickets of the same type. Returned structure is below.
    *   
    *   ```text
    *     [
    *       [0] => [
    *         'event_ticket_type_id'    => (int)     Ticket type ID of this set of tickets
    *         'event_ticket_type_name'  => (string)  Name of this ticket type
    *         'event_ticket_type_price' => (int)     Price of this ticket type
    *         'quantity'                => (int)     The quantity of tickets in this ticket type set
    *         'ids'                     => (string)  A comma-separated list of each ticket ID in this set
    *         'id'                      => (string)  Event ID
    *         'name'                    => (string)  Event name
    *         'host'                    => (string)  Event host
    *         'start'                   => (string)  Event start datetime in Y-m-d H:i:s format.
    *         'venue'                   => (string)  Event venue name
    *         'address_1'               => (string)  Line 1 of the event venue address
    *         'address_2'               => (string)  Line 2 of the event venue address, null by default.
    *         'city'                    => (string)  City of the event venue address.
    *         'postcode'                => (string)  Postcode of the event venue address.
    *         'end'                     => (string)  Datetime event is to end in Y-m-d H:i:s format.
    *         'description'             => (string)  HTML event description
    *         'image'                   => (string)  URL to event image
    *         'private'                 => (boolean) Whether this event is private or not.
    *         'fb_id'                   => (string)  The Facebook ID of the corresponding Facebook event, if there is one.
    *         'user_id'                 => (int)     The user that created this event.
    *         'created'                 => (string)  Datetime this event was created, in Y-m-d H:i:s format.
    *       ],
    *       [1] => [
    *         ...
    *       ],
    *       ...
    *     ]
    *   ```
    */
    function get_user_tickets($userId){
        global $db;
        if(isset($userId) && $userId){
            if(!user_exists($userId))
                return array("err"=>"User id ".$userId." not recognised","status"=>404);
        } else {
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $userId = $_SESSION['user']['id'];
        }
        try{
            //$res = $db->query("SELECT * FROM tickets WHERE user_id = $userId")->fetchAll(PDO::FETCH_ASSOC);
            $query = <<<EOF
SELECT
    event_ticket_type_id,
    event_ticket_types.name AS event_ticket_type_name,
    event_ticket_types.price AS event_ticket_type_price,
    COUNT(event_ticket_type_id) AS quantity,
    GROUP_CONCAT(tickets.id SEPARATOR ',') AS ids,
    events.*
FROM `tickets`
INNER JOIN `event_ticket_types` on tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` on event_ticket_types.event_id = events.id
WHERE
    tickets.user_id = $userId AND
    sold_ticket IS NULL AND
    transferred_ticket IS NULL AND
    events.start > DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY event_ticket_type_id
ORDER BY events.start ASC, event_ticket_types.id ASC
EOF;
            $res = $db->query($query);
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets all tickets purchased by this user that have not been resold or transferred and are for upcoming events.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $userId The ID of the user whose tickets are to be fetched. Default is the ID of the current logged in user.
    *
    * @return array[] Returns an array of arrays where each item in the array is the information regarding a set of purchased tickets of the same type. Returned structure is below.
    *   
    *   ```text
    *     [
    *       [0] => [
    *         'event_ticket_type_id'    => (string) Ticket type ID of this set of tickets
    *         'event_ticket_type_name'  => (string) Name of this ticket type
    *         'event_ticket_type_price' => (string) Price of this ticket type
    *         'quantity'                => (int)    The quantity of tickets in this ticket type set
    *         'ids'                     => (string) A comma-separated list of each ticket ID in this set
    *         'id'                      => (string) Event ID
    *         'name'                    => (string) Event name
    *         'host'                    => (string) Event host
    *         'start'                   => (string) Event start datetime in Y-m-d H:i:s format.
    *         'venue'                   => (string) Event venue name
    *         'address_1'               => (string) Line 1 of the event venue address
    *         'address_2'               => (string) Line 2 of the event venue address, null by default.
    *         'city'                    => (string) City of the event venue address.
    *         'postcode'                => (string) Postcode of the event venue address.
    *         'end'                     => (string) Datetime event is to end in Y-m-d H:i:s format.
    *         'description'             => (string) HTML event description
    *         'image'                   => (string) URL to event image
    *         'private'                 => (boolean) Whether this event is private or not.
    *         'fb_id'                   => (string) The Facebook ID of the corresponding Facebook event, if there is one.
    *         'user_id'                 => (string) The user that created this event.
    *         'created'                 => (string) Datetime this event was created, in Y-m-d H:i:s format.
    *       ],
    *       [1] => [
    *         ...
    *       ],
    *       ...
    *     ]
    *   ```
    *
    * @todo Code repitition here between `get_user_tickets` and `get_user_upcoming_tickets`. Improve to reduce code repitition, perhaps one function with an `$upcoming` flag, or a `$from` argument that defaults to the current time.
    */
    function get_user_upcoming_tickets($userId){
        global $db;
        if(isset($userId) && $userId){
            if(!user_exists($userId))
                return array("err"=>"User id ".$userId." not recognised","status"=>404);
        } else {
            if (session_status() == PHP_SESSION_NONE)
                session_start();
            if(!is_array($_SESSION['user']) || !$_SESSION['user']['id'])
                return array("err"=>"User not logged in","status"=>500);
            else
                $userId = $_SESSION['user']['id'];
        }
        try{
            /*$query = <<<EOF
SELECT tickets.*
FROM `tickets`
INNER JOIN `event_ticket_types` on tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` on event_ticket_types.event_id = events.id
WHERE tickets.user_id = $userId AND (
    (events.end IS NOT NULL AND events.end > NOW())
    OR
    (events.end IS NULL AND events.start > DATE_SUB(NOW(), INTERVAL 4 HOUR))
)
ORDER BY events.start ASC
EOF;*/
            $query = <<<EOF
SELECT
    event_ticket_type_id,
    event_ticket_types.name AS event_ticket_type_name,
    event_ticket_types.price AS event_ticket_type_price,
    COUNT(event_ticket_type_id) AS quantity,
    GROUP_CONCAT(tickets.id SEPARATOR ',') AS ids,
    events.*
FROM `tickets`
INNER JOIN `event_ticket_types` on tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` on event_ticket_types.event_id = events.id
WHERE
    tickets.user_id = $userId AND
    selling_time IS NULL AND
    transferred_ticket IS NULL AND (
        (events.end IS NOT NULL AND events.end > NOW())
        OR
        (events.end IS NULL AND events.start > DATE_SUB(NOW(), INTERVAL 4 HOUR))
    )
GROUP BY event_ticket_type_id
ORDER BY events.start ASC, event_ticket_types.id ASC
EOF;
            $res = $db->query($query);
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    function get_event_tickets($eventId){
        global $db;
        if(!isset($eventId) || !$eventId){
            return array("err"=>"No event ID passed","status"=>500);
        }
        $query = <<<EOF
SELECT tickets.*
FROM tickets
INNER JOIN `event_ticket_types` ON tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` ON event_ticket_types.event_id = events.id
WHERE events.id = $eventId
EOF;
        try{
            $res = $db->query($query);
            if($res)
                return $res->fetchAll(PDO::FETCH_ASSOC);
            else
                return array("err"=>$db->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Updates a row in the `tickets` table with the given data.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to update in the `tickets` table.
    * @param mixed[] $data An array with key-value pairs corresponding to the columns which need updating.
    *
    * @return mixed[] On success, returns the newly updated row (including all columns in the table), or on error it returns data describing the error (`err` and `status` keys).
    */
    function update_ticket($id, $data){
        global $db;
        $allowed_keys = array("id","event_ticket_type_id","user_id","charge_id","time","bought_ticket","selling_time","selling_price","sold_ticket","transferred_from_ticket","transferring_to","transfer_price","transfer_time","transferred_ticket","used");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`= ?, `',$keys).'` = ?';
        try{
            $stmt = $db->prepare("UPDATE `tickets` SET $fields WHERE `id` = ?");
            if($stmt->execute(array_merge(array_values($data),array($id)))){
                $stmt = $db->prepare("SELECT * FROM `tickets` WHERE id = ?");
                $stmt->execute($data["id"] ? $data["id"] : array($id));
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Gets the cheapest reselling ticket of the given ticket type if one exists.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $event_ticket_type_id The ID of the ticket type to match against any reselling tickets.
    *
    * @return mixed[] If one or more reselling tickets of this ticket type are found, it returns the row data (all columns in the table) for the ticket with the smallest reselling price, if several the ticket that was put up for resale first is chosen. Returns false if no such ticket exists and on error it returns an array describing the error (`err` and `status` keys).
    */
    function get_reselling($event_ticket_type_id){
        global $db;
        $stmt = $db->prepare('SELECT * FROM tickets WHERE event_ticket_type_id = ? AND selling_price IS NOT NULL AND sold_ticket IS NULL ORDER BY selling_price ASC, selling_time ASC LIMIT 1');
        $stmt->bindParam(1, $event_ticket_type_id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Deletes the row with the given ID from the `tickets` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to delete from the `tickets` table.
    *
    * @return mixed[] On success, returns a simple array with one key (`success`) and gives it the value `true`. On error it returns data describing the error (`err` and `status` keys).
    */
    function delete_ticket($id){
        global $db;
        $stmt = $db->prepare('DELETE FROM ticket WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id);
        try{
            if($stmt->execute()){
                return array("success"=>true);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }
    /** @}*/

    /* ==================================================================================== *
       Multiple Tables
     * ==================================================================================== */

    /**
    * @defgroup io-cross Cross-table IO
    * @brief Functions that communicate with the `tktpass` database to get useful information from several tables at once.
    * @{
    */

    /**
    * Gets all activity (bought, passed, resold tickets etc) of the given event.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $eventId The ID of the event whose activity is to be returned.
    *
    * @return array[] Returns an array of arrays where each item in the array is the information regarding a set of purchased tickets of the same type. Returned structure is below.
    *   
    *   ```text
    *     [
    *       [0]=> [
    *         'user_id'               => (int)    The ID of the user performing this action
    *         'action'                => (string) The action (one of 'bought', 'resold', 'passed', 'listed for sale', or 'created the event')
    *         'event_ticket_type_ids' => (string) A comma-separated list of one or more ticket type IDs corresponding to the tickets related to this action
    *         'to'                    => (int)    For 'resold' and 'passed' actions, this is the ID of the other user involved in this action
    *         'time'                  => (string) Y-m-d H:i:s datetime this action took place
    *       ],
    *       [1]=> [
    *         ...
    *       ],
    *       ...
    *     ]
    *   ```
    *
    * @todo Return user's first name here rather than just user ID?
    */
    function get_event_activity($eventId){
        global $db;
        if(!isset($eventId) || !$eventId){
            return array("err"=>"No event ID passed","status"=>500);
        }
        $activity = array();

        //bought actions
        $query = <<<EOF
SELECT
    tickets.user_id,
    'bought' AS action,
    GROUP_CONCAT(tickets.event_ticket_type_id SEPARATOR ',') AS event_ticket_type_ids,
    tickets.time
FROM tickets
INNER JOIN `event_ticket_types` ON tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` ON event_ticket_types.event_id = events.id
WHERE
    events.id = $eventId AND
    tickets.bought_ticket IS NULL AND
    tickets.transferred_from_ticket IS NULL
GROUP BY tickets.time, tickets.user_id
ORDER BY tickets.time DESC
EOF;
        $res = $db->query($query);
        if($res)
            $activity = array_merge($activity, $res->fetchAll(PDO::FETCH_ASSOC));
        else
            return array("err"=>$db->errorInfo()[2],"status"=>500);

        //resold actions
        $query = <<<EOF
SELECT
    (SELECT MIN(tickets2.user_id) FROM tickets AS tickets2 WHERE tickets2.id in (GROUP_CONCAT(tickets1.bought_ticket SEPARATOR ','))) AS `user_id`,
    'resold' AS action,
    GROUP_CONCAT(tickets1.event_ticket_type_id SEPARATOR ',') as event_ticket_type_ids,
    tickets1.user_id AS `to`,
    tickets1.time
FROM tickets AS tickets1
INNER JOIN `event_ticket_types` ON tickets1.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` ON event_ticket_types.event_id = events.id
WHERE
    events.id = $eventId AND
    tickets1.bought_ticket IS NOT NULL
GROUP BY tickets1.time, tickets1.user_id
ORDER BY tickets1.time DESC
EOF;
        $res = $db->query($query);
        if($res)
            $activity = array_merge($activity, $res->fetchAll(PDO::FETCH_ASSOC));
        else
            return array("err"=>$db->errorInfo()[2],"status"=>500);

        //passed actions
        $query = <<<EOF
SELECT
    (SELECT MIN(tickets2.user_id) FROM tickets AS tickets2 WHERE tickets2.id in (GROUP_CONCAT(tickets1.transferred_from_ticket SEPARATOR ','))) AS `user_id`,
    'passed' AS action,
    GROUP_CONCAT(tickets1.event_ticket_type_id SEPARATOR ',') as event_ticket_type_ids,
    tickets1.user_id AS `to`,
    tickets1.time
FROM tickets AS tickets1
INNER JOIN `event_ticket_types` ON tickets1.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` ON event_ticket_types.event_id = events.id
WHERE
    events.id = $eventId AND
    tickets1.transferred_from_ticket IS NOT NULL
GROUP BY tickets1.time, tickets1.user_id
ORDER BY tickets1.time DESC
EOF;
        $res = $db->query($query);
        if($res)
            $activity = array_merge($activity, $res->fetchAll(PDO::FETCH_ASSOC));
        else
            return array("err"=>$db->errorInfo()[2],"status"=>500);

        //listed for sale actions
        $query = <<<EOF
SELECT
    tickets.user_id,
    'listed for sale' AS action,
    GROUP_CONCAT(tickets.event_ticket_type_id SEPARATOR ',') AS event_ticket_type_ids,
    tickets.selling_time AS time
FROM tickets
INNER JOIN `event_ticket_types` ON tickets.event_ticket_type_id = event_ticket_types.id
INNER JOIN `events` ON event_ticket_types.event_id = events.id
WHERE
    events.id = $eventId AND
    tickets.sold_ticket IS NULL AND
    tickets.selling_time IS NOT NULL
GROUP BY tickets.selling_time, tickets.user_id
ORDER BY tickets.selling_time DESC
EOF;
        $res = $db->query($query);
        if($res)
            $activity = array_merge($activity, $res->fetchAll(PDO::FETCH_ASSOC));
        else
            return array("err"=>$db->errorInfo()[2],"status"=>500);

        //created the event action
        $event = get_event($eventId);
        array_push($activity, array("user_id"=>$event["user_id"],"action"=>"created the event","time"=>$event["created"]));

        //sort in chronilogical order
        function cmp($a, $b){
            if ($a["time"] == $b["time"])
                return 0;
            return ((new DateTime($a["time"])) < (new DateTime($b["time"]))) ? 1 : -1;
        }
        usort($activity, "cmp");

        return $activity;
    }

    /**
    * Gets sale statistics for the given event.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $eventId The ID of the event whose sale statistics are to be returned.
    *
    * @return array[] Returns an associative array where each key corresponds to specific data regarding sales statistics. Returned structure is below.
    *   
    *   ```text
    *     [
    *       "weekSales" => [
    *         [ [0,55], [1,48], [2,60], [3,36], [4,40], [5,60], [6,50], [7,51] ],
    *         [ [0,0], [1,0], [2,0], [3,0], [4,0], [5,67], [6,57], [7,59] ]
    *       ],
    *       "revenue" => [ [0,0], [1, 10], [2, 26], [3, 26], [4, 36], [5, 38], [6,51] ],
    *       "gender" => [ "male" => 38, "female" => 40, "unknown" => 22 ],
    *       "age" => [
    *           [0,11], //Under 18
    *           [1,15], //18-21
    *           [2,25], //22-25
    *           [3,24], //26-30
    *           [4,13]  //Over 30
    *       )
    *     ]
    *   ```
    *
    * @bug Just a placeholder function that returns the same data every time. Needs implementing.
    */
    function get_event_stats($eventId){
        global $db;
        if(!isset($eventId) || !$eventId){
            return array("err"=>"No event ID passed","status"=>500);
        }
        //$stats = ...
        $stats = array(
            "weekSales"=>array(
                array(array(0,55),array(1,48),array(2,60),array(3,36),array(4,40),array(5,60),array(6,50),array(7,51)),
                array(array(0,0),array(1,0),array(2,0),array(3,0),array(4,0),array(5,67),array(6,57),array(7,59))
            ),
            "revenue"=>array(array(0,0),array(1,10),array(2,26),array(3,26),array(4,36),array(5,38),array(6,51)),
            "gender"=>array("male"=>38,"female"=>40,"unknown"=>22),
            "age"=>array(
                array(0,11), //Under 18
                array(1,15), //18-21
                array(2,25), //22-25
                array(3,24), //26-30
                array(4,13)  //Over 30
            )
        );
        return $stats;
    }
    /** @}*/

    /* ==================================================================================== *
       User Recovery Table
     * ==================================================================================== */

    /**
    * @defgroup io-recovery User Recovery Table IO
    * @brief All functions for communicating with the `user_recovery` table of the `tktpass` database.
    * @{
    */

    /**
    * Inserts a new row in the `user_recovery` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param mixed[] $data New row data to be inserted into the table. Required.
    *   ```text
    *   $data = [
          'user_id'  => (int) ID of the user who requested this password recovery. Required.
          'selector' => (string) Random string given to the user, bin2hex(random_bytes(8)). Required.
          'hash'     => (string) Hash of the 32 bytes sent to the user as validator, hash('sha256',$bytes). Required.
          'expires'  => (string) Y-m-d H:i:s datetime for when this recovery request will expire, default is 1 hour from now.
    *   ]
    *   ```
    *
    * @return mixed[] Returns the newly inserted row on success (including its assigned ID in the table) or on error it returns data describing the error.
    *   
    *   On success
    *   ```text
    *     $row = [
            'id'       => (int)    ID of the entry in the table
            'user_id'  => (int)    ID of the user who requested this password recovery. Required.
            'selector' => (string) Random string given to the user, bin2hex(random_bytes(8)). Required.
            'hash'     => (string) Hash of the 32 bytes sent to the user as validator, hash('sha256',$bytes). Required.
            'expires'  => (string) Y-m-d H:i:s datetime for when this recovery request will expire, default is 1 hour from now.
            'token'    => (string) Form token generated when the email link is clicked, this is compared to the subsequent form submission to ensure the submission is coming from the right source.
    *     ]
    *   ```
    *   On error
    *   ```text
    *     $error = [
    *       'err'    => (string) Description of the error that occurred.
    *       'status' => (int)    HTTP status code to return the error with.
    *     ]
    *   ```
    */
    function insert_user_recovery($data){
        global $db;
        $required_keys = array("user_id","selector","hash");
        foreach($required_keys as $required){
          if(!isset($data[$required]) || !$data[$required])
            return array("err"=>"Missing required key: ".$required,"status"=>400);
        }
        $allowed_keys = array("user_id","selector","hash","expires");
        $keys = array_keys($data);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        if(!isset($data["expires"]) || !$data["expires"]){
          $expires = (new DateTime())->add(new DateInterval('PT01H')); // 1 hour
          $data["expires"] = $expires->format('Y-m-d H:i:s');
          $keys = array_keys($data);
        }
        $fields = '`'.implode('`, `',$keys).'`';
        $placeholder = rtrim(str_repeat('?,', count($keys)), ",");
        try{
            $stmt = $db->prepare("INSERT INTO `user_recovery` ($fields) VALUES ($placeholder)");
            if($stmt->execute(array_values($data))){
                $id = $db->lastInsertId();
                $res = $db->query("SELECT * FROM `user_recovery` WHERE id = ".$id);
                if($res)
                    return $res->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$db->errorInfo()[2],"status"=>500);
            } else{
                return array("err"=>"Execute failed: ".$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Checks whether a row with the given ID exists in the `user_recovery` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID to check for in the table.
    *
    * @return boolean Whether a row with the given ID does exists in the `user_recovery` table or not.
    *
    * @note Not really used. Redundant?
    */
    function user_recovery_exists($id){
        global $db;
        $stmt = $db->prepare('SELECT COUNT(id) FROM user_recovery WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, intval($id), PDO::PARAM_INT);
        if($stmt->execute()){
            return (bool)($stmt->fetchColumn());
        } else
            return array("err"=>$stmt->errorInfo()[2],"status"=>500);
    }

    /**
    * Gets the row with the given ID from the `user_recovery` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID of the row to fetch from the `user_recovery` table.
    *
    * @return mixed[] If a row with the given ID does exist, returns a mixed[] with the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    *
    * @note Not really used. Redundant?
    */
    function get_user_recovery($id){
        global $db;
        $stmt = $db->prepare('SELECT * FROM `user_recovery` WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Validates whether a row with the given selector exists in the `user_recovery` table and matches the given validator against the hash in the table.
    * If `$token` is provided, also matches this against the token in the table, otherwise ensures there is no token in the table.
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $selector The selector string for this user recovery request.
    * @param string $validator The validator string from the user recovery email.
    * @param string $token Form submission token, if this is a form submission.
    *
    * @return mixed[] If the given strings sucessfully validate, returns a mixed[] of the row data. If no such row exists, returns false. On any other error, returns a mixed[] with a string description of the error under the `err` key and the HTTP status code under the `status` key.
    */
    function validate_user_recovery($selector, $validator, $token){
        global $db;
        $stmt = $db->prepare('SELECT * FROM `user_recovery` WHERE selector = ?');
        $stmt->bindParam(1, $selector, PDO::PARAM_STR);
        try{
            if($stmt->execute()){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!$row || empty($row))
                    return array("err"=>"Reset link not recognised, perhaps it has expired or been used. Please request a new email if you still need to reset your password.","status"=>400);
                if((new DateTime($row['expires'])) < (new DateTime())){
                  return array("err"=>"This link has expired. Please request a new email if you still need to reset your password.","status"=>400);
                }
                if(!is_null($row['token'])){
                  if(!isset($token) || $token!==$row['token'])
                    return array("err"=>"This link has been used. Please request a new email if you need to reset your password again.","status"=>400);
                }
                $hash = hash('sha256', hex2bin($validator));
                if(hash_equals($hash, $row['hash'])){
                  return $row;
                } else {
                  return array("err"=>"Reset link not recognised. Please request a new email if you still need to reset your password.","status"=>400);
                }
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /**
    * Sets a user recovery request as used by adding a form submission token to the database. Only a form submission with this token will be accepted.
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param int $id The ID of the row to create a token for.
    *
    * @return mixed[] On success returns array("id"=>$id,"token"=>$token), usual error array on error;
    */
    function set_used_user_recovery($id){
        global $db;
        $token = bin2hex(random_bytes(8));
        try{
            $stmt = $db->prepare("UPDATE `user_recovery` SET `token` = ? WHERE `id` = ?");
            if($stmt->execute(array($token,$id))){
                return array("id"=>$id,"token"=>$token);
            }
            else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }

    /*
    * Updates a row in the `user_recovery` table with the given data.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to update in the `user_recovery` table.
    * @param mixed[] $data An array with key-value pairs corresponding to the columns which need updating.
    *
    * @return mixed[] On success, returns the newly updated row (including all columns in the table), or on error it returns data describing the error (`err` and `status` keys).
    *
    * @note Not used, and is in fact a risk. So removed.
    *
    function update_user_recovery($id, $data){
        global $db;
        $keys = array_keys($data);
        $allowed_keys = array("id","user_id","selector","hash","token");
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key: ".$key,"status"=>400);
          }
        }
        $fields = '`'.implode('`= ?, `',$keys).'` = ?';
        try{
            $stmt = $db->prepare("UPDATE `user_recovery` SET $fields WHERE `id` = ?");
            if($stmt->execute(array_merge(array_values($data),array($id)))){
                $stmt = $db->prepare("SELECT * FROM `user_recovery` WHERE id = ?");
                if($stmt->execute($data["id"] ? array($data["id"]) : array($id)))
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                else
                    return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
            else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }*/

    /**
    * Deletes the row with the given ID from the `user_recovery` table.
    *
    *
    * @author  Alex Taylor <alex@taylrr.co.uk>
    *
    * @since 1.0
    *
    * @param string $id The ID of the row to delete from the `user_recovery` table.
    *
    * @return mixed[] On success, returns a simple array with one key (`success`) and gives it the value `true`. On error it returns data describing the error (`err` and `status` keys).
    */
    function delete_user_recovery($id){
        global $db;
        $stmt = $db->prepare('DELETE FROM user_recovery WHERE id = ? LIMIT 1');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try{
            if($stmt->execute()){
                return array("success"=>true);
            } else{
                return array("err"=>$stmt->errorInfo()[2],"status"=>500);
            }
        } catch(Exception $e){
            return array("err"=>$e->getMessage()?$e->getMessage():"Unknown error occured","status"=>500);
        }
    }
    /** @}*/

/** @}*/