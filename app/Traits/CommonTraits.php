<?php
namespace App\Traits;
/**
 * Created by PhpStorm.
 * User: BNC
 * Date: 9/25/2019
 * Time: 9:37 PM
 */
use App\DataLog;
use App\LoginLog;
trait CommonTraits
{
    public function crate_log($data,$module,$operation){
        $model = new DataLog();
        $data = json_encode($data);
        $input_data = array(
            'data'  =>  $data,
            'module'    => $module,
            'operation' =>  $operation,
            'performed_by'  =>  auth()->user()->id,
            'performer_name'    => auth()->user()->name,
            'performed_at'      =>  date('d-m-Y H:i:s a')
        );
        $save_log = $model->insert($input_data);
        if ($save_log){
            return true;
        } else{
            return false;
        }
    }
    /* trait for storing login info start */
    public function crate_login_log($email,$password){
        $model = new LoginLog();
        $input_data = array(
            'email_id'      =>  $email,
            'password'      => $password,
            'performed_at'  =>  date('d-m-Y H:i:s a')
        );
        $is_exist = $model->where(array('email_id'=>$email))->count();
        if ($is_exist > 0){
            $save_log = $model->where(array('email_id'=>$email))->update($input_data);
        }else{
            $save_log = $model->insert($input_data);
        }
        if ($save_log){
            return true;
        } else{
            return false;
        }
    }
    /* trait for storing login info end  */
    public function CustomerPushNotification($data)
    {
        $headers = array
        (
            'Authorization: key=AAAAV9Mf4wo:APA91bEB_q0NbcDqZMPpGPYDg4DvfBkbBv7vldds2WgOSYEXnEP_cmoynGlVd2BkvH7jALa1ebA5-U6ZWD72fWLaNtfo_Ib-1E3-oq41V-U2TgR82mn-Z2CC41uNq7MksTrd9RtpKpJu',
            'Content-Type: application/json'
        );
        $fields = array
        (
            'to'            => '/topics/SmsSend',
            'data'          => $data,
            "priority"      => "high"
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        $result = curl_exec($ch);
        if ($result === FALSE)

        {
            die('FCM Send Error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $ch;
    }
}