<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Employee;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Font;

/**
 * TT бўлим 7 — Маълумотнома .docx экспорти.
 *
 * Times New Roman 12pt, A4.
 * Маржалар: top 1.5cm, bottom 1cm, left 2cm, right 1cm.
 * Файл номи: Malumotnoma_[Familiya]_[Ism]_[YYYY-MM-DD].docx
 */
class MalumotnomaDocxExporter
{
    private const FONT_NAME = 'Times New Roman';

    private const FONT_SIZE = 12;

    // Маржалар (twips: 1 cm = 567 twips)
    private const MARGIN_TOP = 850;    // 1.5 cm

    private const MARGIN_BOTTOM = 567; // 1 cm

    private const MARGIN_LEFT = 1134;  // 2 cm

    private const MARGIN_RIGHT = 567;  // 1 cm

    public function __construct(
        private EmployeeFormatter $formatter,
    ) {}

    public function export(Employee $employee): string
    {
        Settings::setOutputEscapingEnabled(true);

        $data = $this->formatter->format($employee);
        $phpWord = new PhpWord;

        // Стандарт шрифт
        $phpWord->setDefaultFontName(self::FONT_NAME);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);

        $section = $phpWord->addSection([
            'pageSizeW' => 11906, // A4 width (twips)
            'pageSizeH' => 16838, // A4 height (twips)
            'marginTop' => self::MARGIN_TOP,
            'marginBottom' => self::MARGIN_BOTTOM,
            'marginLeft' => self::MARGIN_LEFT,
            'marginRight' => self::MARGIN_RIGHT,
        ]);

        // ===== 1-БЛОК: САРЛАВҲА =====
        $this->renderHeader($section, $data);

        // ===== 2-БЛОК: ШАХСИЙ МАЪЛУМОТЛАР =====
        $this->renderPersonalData($section, $data);

        // ===== 3-БЛОК: МЕҲНАТ ФАОЛИЯТИ =====
        $this->renderWorkHistory($section, $data);

        // ===== 4-БЛОК: ЯҚИН ҚАРИНДОШЛАР =====
        $this->renderRelatives($section, $data);

        // Файлни вақтинча жойга сақлаш
        $filename = $this->generateFilename($employee);
        $path = storage_path("app/private/{$filename}");

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($path);

        return $path;
    }

    public function generateFilename(Employee $employee): string
    {
        $date = now()->format('Y-m-d');

        return "Malumotnoma_{$employee->last_name_cyr}_{$employee->first_name_cyr}_{$date}.docx";
    }

    /**
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  array<string, mixed>  $data
     */
    private function renderHeader($section, array $data): void
    {
        // Заголовок
        $section->addText(
            'МАЪЛУМОТНОМА',
            ['bold' => true, 'size' => 14, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120],
        );

        // Ф.И.Ш.
        $section->addText(
            $data['full_name'],
            ['bold' => true, 'size' => 14, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120],
        );

        // Лавозим
        $section->addText(
            $data['current_position'],
            ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60],
        );

        // Тайинланган сана
        $section->addText(
            $data['position_start_date'],
            ['size' => self::FONT_SIZE, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200],
        );
    }

    /**
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  array<string, mixed>  $data
     */
    private function renderPersonalData($section, array $data): void
    {
        $fontStyle = ['size' => self::FONT_SIZE, 'name' => self::FONT_NAME];
        $boldFont = ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME];

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'unit' => TblWidth::TWIP,
        ]);

        $fields = [
            ['Туғилган йили ва жойи', "{$data['birth_date']}, {$data['birth_place']}"],
            ['Миллати', $data['nationality']],
            ['Партиявийлиги', $data['party_affiliation']],
            ['Маълумоти', "{$data['education_level']}, {$data['education_completion']}"],
            ['Маълумоти бўйича мутахассислиги', $data['specialty_by_education']],
            ['Илмий даражаси', $data['academic_degree']],
            ['Илмий унвони', $data['academic_title']],
            ['Қайси чет тилларини билади', $data['foreign_languages']],
            ['Давлат мукофотлари билан тақдирланганми', $data['state_awards']],
            ['Сайланадиган органларнинг депутати ёки аъзосими', $data['elected_body_member']],
        ];

        foreach ($fields as [$label, $value]) {
            $row = $table->addRow();
            $row->addCell(3600)->addText($label, $boldFont);
            $row->addCell(6000)->addText((string) $value, $fontStyle);
        }

        $section->addTextBreak(1);
    }

    /**
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  array<string, mixed>  $data
     */
    private function renderWorkHistory($section, array $data): void
    {
        $section->addText(
            'МЕҲНАТ ФАОЛИЯТИ',
            ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120],
        );

        $fontStyle = ['size' => self::FONT_SIZE, 'name' => self::FONT_NAME];

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'unit' => TblWidth::TWIP,
        ]);

        // Сарлавҳа қатори
        $headerRow = $table->addRow();
        $headerRow->addCell(2400)->addText('Йиллар', ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME], ['alignment' => Jc::CENTER]);
        $headerRow->addCell(7200)->addText('Лавозимлари', ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME], ['alignment' => Jc::CENTER]);

        /** @var array<int, array{years: string, description: string}> $workHistory */
        $workHistory = $data['work_history'];

        foreach ($workHistory as $item) {
            $row = $table->addRow();
            $row->addCell(2400)->addText($item['years'], $fontStyle);
            $row->addCell(7200)->addText($item['description'], $fontStyle);
        }

        $section->addTextBreak(1);
    }

    /**
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  array<string, mixed>  $data
     */
    private function renderRelatives($section, array $data): void
    {
        $section->addText(
            'ЯҚИН ҚАРИНДОШЛАРИ ҲАҚИДА МАЪЛУМОТ',
            ['bold' => true, 'size' => self::FONT_SIZE, 'name' => self::FONT_NAME],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120],
        );

        $fontStyle = ['size' => 10, 'name' => self::FONT_NAME];
        $headerFont = ['bold' => true, 'size' => 10, 'name' => self::FONT_NAME];

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'unit' => TblWidth::TWIP,
        ]);

        // Сарлавҳа
        $headerRow = $table->addRow();
        $headerRow->addCell(1400)->addText('Қариндошлиги', $headerFont, ['alignment' => Jc::CENTER]);
        $headerRow->addCell(2200)->addText('Ф.И.Ш.', $headerFont, ['alignment' => Jc::CENTER]);
        $headerRow->addCell(1800)->addText('Туғилган йили ва жойи', $headerFont, ['alignment' => Jc::CENTER]);
        $headerRow->addCell(2400)->addText('Иш жойи ва лавозими', $headerFont, ['alignment' => Jc::CENTER]);
        $headerRow->addCell(1800)->addText('Турар жойи', $headerFont, ['alignment' => Jc::CENTER]);

        /** @var array<int, array{relationship: string, full_name: string, birth_year_place: string, workplace: string, residence: string}> $relatives */
        $relatives = $data['relatives'];

        foreach ($relatives as $rel) {
            $row = $table->addRow();
            $row->addCell(1400)->addText($rel['relationship'], $fontStyle);
            $row->addCell(2200)->addText($rel['full_name'], $fontStyle);
            $row->addCell(1800)->addText($rel['birth_year_place'], $fontStyle);
            $row->addCell(2400)->addText($rel['workplace'], $fontStyle);
            $row->addCell(1800)->addText($rel['residence'], $fontStyle);
        }
    }
}
