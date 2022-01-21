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
        $this->attributs['status'] = 'pending';
        self::save();
    }

    /** 
     * Set status to Success
     * 
     * @return void
    */
    Public function setStatusSuccsess()
    {
        $this->attributs['status'] = 'Success';
        self::save();

    /** 
     * Set status to Failed
     * 
     * @return void
    */
    Public function setStatusFailed()
    {
        $this->attributs['status'] = 'Failed';
        self::save();
    
    /** 
     * Set status to Expired
     * 
     * @return void
    */
    Public function setStatusExpired()
    {
        $this->attributs['status'] = 'Expired';
        self::save();
    
}
