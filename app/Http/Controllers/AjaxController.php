<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    protected $loc, $my_class;

    public function __construct(LocationRepo $loc, MyClassRepo $my_class)
    {
        $this->loc = $loc;
        $this->my_class = $my_class;
    }

    /**
     * Return suggested admission number: {SCHOOL_ACRONYM}/{CLASS_SHORTNAME}/{NEXT_SERIAL}
     * e.g. XING/G8/009
     * Uses max(existing serials) + 1 so deleted students don't reuse IDs.
     */
    public function get_next_admission_number($class_id): JsonResponse
    {
        $class = MyClass::find($class_id);
        if (!$class) {
            return response()->json(['admission_number' => '', 'error' => 'Invalid class'], 404);
        }

        $acronym = strtoupper((string) (Qs::getAppCode() ?: 'EMA'));
        $classShort = $this->shortenClassName($class->name ?? '');

        $admNos = StudentRecord::where('my_class_id', $class_id)->pluck('adm_no')->filter();
        $maxSerial = 0;
        foreach ($admNos as $admNo) {
            $lastSlash = strrchr((string) $admNo, '/');
            $segment = $lastSlash !== false ? substr($lastSlash, 1) : '';
            if ($segment !== '' && ctype_digit($segment)) {
                $n = (int) $segment;
                if ($n > $maxSerial) {
                    $maxSerial = $n;
                }
            }
        }
        $nextSerial = str_pad((string) ($maxSerial + 1), 3, '0', STR_PAD_LEFT);
        $admissionNumber = $acronym . '/' . $classShort . '/' . $nextSerial;

        return response()->json(['admission_number' => $admissionNumber]);
    }

    /**
     * Shorten class name e.g. "Grade 8" -> "G8", "Primary 1" -> "P1"
     * Safe for null, empty, or unusual names.
     */
    private function shortenClassName(string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'CL';
        }
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $short = '';
        foreach ($parts as $part) {
            $part = (string) $part;
            if (preg_match('/^\d+$/', $part)) {
                $short .= $part;
            } elseif (preg_match('/^([A-Za-z]).*$/', $part, $m)) {
                $short .= strtoupper($m[1]);
            }
        }
        if ($short === '' && $name !== '') {
            $fallback = preg_replace('/\s+/', '', $name);
            $short = $fallback !== '' ? strtoupper(substr($fallback, 0, 2)) : 'CL';
        }
        return $short !== '' ? $short : 'CL';
    }

    public function get_lga($state_id)
    {
//        $state_id = Qs::decodeHash($state_id);
//        return ['id' => Qs::hash($q->id), 'name' => $q->name];

        $lgas = $this->loc->getLGAs($state_id);
        return $data = $lgas->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_sections($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        return $sections = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_subjects($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        $subjects = $this->my_class->findSubjectByClass($class_id);

        if(Qs::userIsTeacher()){
            $subjects = $this->my_class->findSubjectByTeacher(Auth::user()->id)->where('my_class_id', $class_id);
        }

        $d['sections'] = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
        $d['subjects'] = $subjects->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();

        return $d;
    }

}
