<?php

namespace Tests\Unit;

use App\Filament\Resources\GainsProfiles\Schemas\GainsProfileForm;
use PHPUnit\Framework\TestCase;

class GainsProfileRichEditorFillTest extends TestCase
{
    public function test_it_wraps_json_scalar_values_as_html_before_filling_rich_editors(): void
    {
        $this->assertSame('<p>2024</p>', GainsProfileForm::normalizeRichEditorValueForFill('2024'));
        $this->assertSame('<p>true</p>', GainsProfileForm::normalizeRichEditorValueForFill('true'));
        $this->assertSame('<p>&quot;hello&quot;</p>', GainsProfileForm::normalizeRichEditorValueForFill('"hello"'));
    }

    public function test_it_wraps_non_tiptap_json_values_as_html_before_filling_rich_editors(): void
    {
        $this->assertSame(
            '<p>{&quot;foo&quot;:1}</p>',
            GainsProfileForm::normalizeRichEditorValueForFill('{"foo":1}'),
        );
    }

    public function test_it_leaves_html_plain_text_and_tiptap_documents_unchanged(): void
    {
        $document = '{"type":"doc","content":[]}';

        $this->assertSame('<p>Existing HTML</p>', GainsProfileForm::normalizeRichEditorValueForFill('<p>Existing HTML</p>'));
        $this->assertSame('A normal sentence', GainsProfileForm::normalizeRichEditorValueForFill('A normal sentence'));
        $this->assertSame($document, GainsProfileForm::normalizeRichEditorValueForFill($document));
    }

    public function test_it_only_normalizes_registered_rich_editor_fields_in_form_data(): void
    {
        $data = GainsProfileForm::normalizeRichEditorDataForFill([
            'full_name' => '2024',
            'g_goals' => '2024',
        ]);

        $this->assertSame('2024', $data['full_name']);
        $this->assertSame('<p>2024</p>', $data['g_goals']);
    }
}
