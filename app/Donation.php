<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded =[];

    /** 
     * Set status to Pending
     * 
     * @return void
    */
    Public function setStatusPending()
    {
        $this->attributes['status'] = 'pending';
        // self::save();
        $this->save();
    }

    /** 
     * Set status to Success
     * 
     * @return void
    */
    Public function setStatusSuccsess()
    {
        $this->attributes['status'] = 'Success';
        // self::save();
        $this->save();
    }
    /** 
     * Set status to Failed
     * 
     * @return void
    */
    Public function setStatusFailed()
    {
        $this->attributes['status'] = 'Failed';
        // self::save();
        $this->save();
    }
    /** 
     * Set status to Expired
     * 
     * @return void
    */
    Public function setStatusExpired()
    {
        $this->attributes['status'] = 'Expired';
        // self::save();
        $this->save();
    }
}
