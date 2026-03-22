<?php

namespace App\Repositories;

use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\StudentRecord;

class MarkRepo
{
    public function getGrade($total, $class_type_id)
    {
        if($total < 1) { return NULL; }

        $grades = Grade::where(['class_type_id' => $class_type_id])->get();

        if($grades->count() > 0){
            $gr = $grades->where('mark_from', '<=', $total)->where('mark_to', '>=', $total);
            return $gr->count() > 0 ? $gr->first() : $this->getGrade2($total);
        }
        return $this->getGrade2($total);
    }

    public function getGrade2($total)
    {
        $grades = Grade::whereNull('class_type_id')->get();
        if($grades->count() > 0){
            return $grades->where('mark_from', '<=', $total)->where('mark_to', '>=', $total)->first();
        }
        return NULL;
    }

    public function getSubTotalTerm($st_id, $sub_id, $term, $class_id, $year)
    {
        $d = ['student_id' => $st_id, 'subject_id' => $sub_id, 'my_class_id' => $class_id, 'term' => $term, 'year' => $year];
        $tex = 'tex' . $term;
        $row = Mark::where($d)->where($tex, '>', 0)->select($tex)->first();
        return $row ? $row->$tex : null;
    }

    public function getExamTotalTerm($term, $st_id, $class_id, $year)
    {
        $d = ['student_id' => $st_id, 'term' => $term, 'my_class_id' => $class_id, 'year' => $year];
        $tex = 'tex' . $term;
        return (int) Mark::where($d)->sum($tex);

      /*  unset($d['exam_id']);
        $mk =Mark::where($d);
        $t1 = $mk->select('tex1')->sum('tex1');
        $t2 = $mk->select('tex2')->sum('tex2');
        $t3 = $mk->select('tex3')->sum('tex3');
        return $t1 + $t2 + $t3;*/
    }

    public function getExamAvgTerm($term, $st_id, $class_id, $sec_id, $year)
    {
        $d = ['student_id' => $st_id, 'term' => $term, 'my_class_id' => $class_id, 'section_id' => $sec_id, 'year' => $year];
        $tex = 'tex' . $term;
        $avg = Mark::where($d)->where($tex, '>', 0)->avg($tex);
        return round((float) $avg, 1);

        /*unset($d['exam_id']);
        $mk = Mark::where($d); $count = 0;

        $t1 = $mk->select('tex1')->avg('tex1');
        $t2 = $mk->select('tex2')->avg('tex2');
        $t3 = $mk->select('tex3')->avg('tex3');

        $count = $t1 ? $count + 1 : $count;
        $count = $t2 ? $count + 1 : $count;
        $count = $t3 ? $count + 1 : $count;

        $avg = $t1 + $t2 + $t3;
        return ($avg > 0) ? round($avg/$count, 1) : 0;*/
    }

    public function getSubCumTotal($tex3, $st_id, $sub_id, $class_id, $year)
    {
        $tex1 = $this->getSubTotalTerm($st_id, $sub_id, 1, $class_id, $year);
        $tex2 = $this->getSubTotalTerm($st_id, $sub_id, 2, $class_id, $year);
        return $tex1 + $tex2 + $tex3;
    }

    public function getSubCumAvg($tex3, $st_id, $sub_id, $class_id, $year)
    {
        $count = 0;
        $tex1 = $this->getSubTotalTerm($st_id, $sub_id, 1, $class_id, $year);
        $count = $tex1 ? $count + 1 : $count;
        $tex2 = $this->getSubTotalTerm($st_id, $sub_id, 2, $class_id, $year);
        $count = $tex2 ? $count + 1 : $count;
        $count = $tex3 ? $count + 1 : $count;
        $total = $tex1 + $tex2 + $tex3;

        return ($total > 0) ? round($total/$count, 1) : 0;
    }

    public function getSubjectMark($term, $class_id, $sub_id, $st_id, $year)
    {
        $d = ['term' => $term, 'my_class_id' => $class_id, 'subject_id' => $sub_id, 'student_id' => $st_id, 'year' => $year];
        $tex = 'tex' . $term;
        $row = Mark::where($d)->select($tex)->first();
        return $row ? $row->$tex : null;
    }

    public function getSubPos($st_id, $term, $class_id, $sub_id, $year)
    {
        $d = ['term' => $term, 'my_class_id' => $class_id, 'subject_id' => $sub_id, 'year' => $year];
        $tex = 'tex' . $term;
        $sub_mk = $this->getSubjectMark($term, $class_id, $sub_id, $st_id, $year);
        $sub_mks = Mark::where($d)->whereNotNull($tex)->orderBy($tex, 'DESC')->pluck($tex);
        return $sub_mks->count() > 0 ? $sub_mks->search($sub_mk) + 1 : null;
    }

    public function countExSubjects($term, $st_id, $class_id, $year)
    {
        $d = ['term' => $term, 'my_class_id' => $class_id, 'student_id' => $st_id, 'year' => $year];
        $tex = 'tex' . $term;
        return Mark::where($d)->whereNotNull($tex)->count();
    }

    public function getClassAvg($term, $class_id, $year)
    {
        $d = ['term' => $term, 'my_class_id' => $class_id, 'year' => $year];
        $tex = 'tex' . $term;
        $avg = Mark::where($d)->avg($tex);
        return round((float) $avg, 1);
    }

    public function getPos($st_id, $term, $class_id, $sec_id, $year)
    {
        $d = ['student_id' => $st_id, 'term' => $term, 'my_class_id' => $class_id, 'section_id' => $sec_id, 'year' => $year];
        $tex = 'tex' . $term;
        $my_mk = Mark::where($d)->sum($tex);
        unset($d['student_id']);
        $students = Mark::where($d)->select('student_id')->distinct()->get();
        $all_mks = [];
        foreach ($students as $s) {
            $all_mks[] = $this->getExamTotalTerm($term, $s->student_id, $class_id, $year);
        }
        rsort($all_mks);
        $idx = array_search($my_mk, $all_mks);
        return $idx !== false ? $idx + 1 : null;
    }

    public function getSubjectIDs($data)
    {
        return Mark::distinct()->select('subject_id')->where($data)->get()->pluck('subject_id');
    }

    public function getStudentIDs($data)
    {
        return Mark::distinct()->select('student_id')->where($data)->get()->pluck('student_id');
    }

    /**
     * Grand total (all subjects) for a student in a term/class - for Class Master aggregation.
     */
    public function getGrandTotal($term, $st_id, $class_id, $year)
    {
        $d = ['term' => $term, 'my_class_id' => $class_id, 'student_id' => $st_id, 'year' => $year];
        $tex = 'tex' . $term;
        return (int) Mark::where($d)->sum($tex);
    }

    /**
     * Compute and save class_pos for all exam_records in the given term/class/section (by grand total).
     */
    public function updateClassPositions($term, $class_id, $section_id, $year)
    {
        $studentIds = $this->getStudentIDs([
            'term' => $term,
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'year' => $year,
        ]);
        $totals = [];
        foreach ($studentIds as $stId) {
            $totals[$stId] = $this->getGrandTotal($term, $stId, $class_id, $year);
        }
        arsort($totals);
        $pos = 1;
        foreach ($totals as $stId => $total) {
            ExamRecord::where([
                'term' => $term,
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'student_id' => $stId,
                'year' => $year,
            ])->update(['class_pos' => $pos]);
            $pos++;
        }
    }
}
