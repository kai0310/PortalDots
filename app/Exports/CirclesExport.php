<?php

namespace App\Exports;

use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CirclesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var Form
     */
    private $customForm;

    /**
     * @var AnswersExport
     */
    private $answersExport;

    public function __construct()
    {
        $this->customForm = CustomForm::getFormByType('circle');
        if (isset($this->customForm)) {
            $this->customForm->load(['questions', 'answers.details']);
            $this->answersExport = new AnswersExport($this->customForm);
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Circle::submitted()->with(['leader', 'users', 'places', 'tags', 'statusSetBy'])->get();
    }

    /**
    * @param Circle $circle
    */
    public function map($circle): array
    {
        $leader = $circle->leader->first();
        $members = [];

        foreach ($circle->users->where('pivot.is_leader', false) as $member) {
            $members[] = "{$member->name}(ID:{$member->id},{$member->student_id})";
        }

        if ($circle->status === 'approved') {
            $status = '受理';
        } elseif ($circle->status === 'rejected') {
            $status = '不受理';
        } else {
            $status = '確認中';
        }

        if (isset($this->customForm)) {
            $answer = $this->customForm->answers->where('circle_id', $circle->id)->first();
        }

        return array_merge(
            [
                $circle->id,
                $circle->name,
                $circle->name_yomi,
                $circle->group_name,
                $circle->group_name_yomi,
                $circle->places->implode('name', ','),
                $circle->tags->implode('name', ','),
                $circle->submitted_at,
                $status,
                $circle->status_set_at,
                $circle->statusSetBy
                ? "{$circle->statusSetBy->name}(ID:{$circle->statusSetBy->id},{$circle->statusSetBy->student_id})"
                : '',
                $circle->created_at,
                $circle->updated_at,
                $circle->notes,
                $leader ? "{$leader->name}(ID:{$leader->id},{$leader->student_id})" : '',
                implode(',', $members),
            ],
            isset($answer) ? $this->answersExport->getDetails($answer) : []
        );
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return array_merge(
            [
                '企画ID',
                '企画名',
                '企画名（よみ）',
                '企画を出店する団体の名称',
                '企画を出店する団体の名称（よみ）',
                '使用場所',
                'タグ',
                '参加登録提出日時',
                '登録受理状況',
                '登録受理状況設定日時',
                '登録受理状況設定ユーザー',
                '作成日時',
                '更新日時',
                'スタッフ用メモ',
                '責任者',
                '学園祭係',
            ],
            isset($this->customForm)
            ? $this->customForm->questions->where('type', '!==', 'heading')->pluck('name')->toArray()
            : [],
        );
    }
}
