<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class StudentsFilter
{
    /**
     * apply function
     * apply filters to UserStudent
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, $filters = [])
    {
        $filters['studentnumber'] = $filters['studentnumber'] ?? false;
        $filters['course'] = $filters['course'] ?? false;
        $filters['year'] = $filters['year'] ?? false;
        $filters['gender'] = $filters['gender'] ?? false;
        $filters['voter'] = $filters['voter'] ?? false;

        $filtered = $builder
            // student_number
            ->when($filters['studentnumber'], function ($query) use ($filters) {
                return $query->where('user_students.student_number', 'like', '%'.$filters['studentnumber'].'%');
            })
            // course
            ->when($filters['course'], function ($query) use ($filters) {
                return $query->where('user_students.course_id', $filters['course']);
            })
            // year - not available
            ->when($filters['year'], function ($query) use ($filters) {
                return $query; // return $query->where('user_students.year', $parameters['year']);
            })
            // gender
            ->when($filters['gender'], function ($query) use ($filters) {
                return $query->where('user_students.sex', $filters['gender']);
            })
            // voter
            ->when($filters['voter'] !== false, function ($query) use ($filters) {
                return $query->where('user_students.can_vote', $filters['voter']);
            });

        return $filtered;
    }
}
