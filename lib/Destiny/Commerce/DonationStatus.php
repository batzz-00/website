<?php
namespace Destiny\Commerce;

abstract class DonationStatus {

    /**
     * Used for when a new donation is created before the order has cleared
     */
    const PENDING = 'Pending';

    /**
     * Used for when a new donation is created before the order has cleared
     */
    const COMPLETED = 'Completed';


}