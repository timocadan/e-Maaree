<?php

namespace App\Models;

use Eloquent;

class MarkConfig extends Eloquent
{
    protected $fillable = [
        'class_type_id',
        'term_id',
        'mark_template_id',
        'school_year',
        'active_slot',
    ];

    protected $casts = [
        'active_slot' => 'integer',
    ];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function template()
    {
        return $this->belongsTo(MarkTemplate::class, 'mark_template_id');
    }

    /**
     * Delegate to template for slots; fallback to default if no template.
     */
    public function slotsForDisplay(): array
    {
        if ($this->mark_template_id && $this->template) {
            return $this->template->slotsForDisplay();
        }
        $t = new MarkTemplate();
        $t->setRawAttributes(['configuration' => json_encode(MarkTemplate::defaultConfiguration())]);
        return $t->slotsForDisplay();
    }

    public function totalMax(): int
    {
        return $this->template ? $this->template->totalMax() : 0;
    }
}
