<?php

namespace App\Helpers;

class ViewHelper
{
    public static function anyFiltersApplied($request, $fields)
    {
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }
        return false;
    }

    public static function getStatusBadgeClass($status)
    {
        return $status ? 'bg-success' : 'bg-secondary';
    }

    public static function getAgreementBadgeClass($status)
    {
        switch ($status) {
            case 'verified':
                return 'bg-success';
            case 'uploaded':
                return 'bg-warning text-dark';
            default:
                return 'bg-secondary';
        }
    }
}
