<?php

namespace App\Filament\Resources\GainsProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;

class GainsProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hồ Sơ 1-2-1')
                    ->tabs([
                        Tab::make('Cá nhân & Gia đình')
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Họ và tên (hiển thị trên thẻ)')
                                    ->required()->columnSpanFull(),
                                TextInput::make('education')
                                    ->label('Học vấn')
                                    ->placeholder('VD: Đại học Kinh tế Quốc dân'),
                                TextInput::make('bni_position')
                                    ->label('Chức vụ BNI')
                                    ->placeholder('VD: Chủ tịch NK20'),
                                TextInput::make('chapter_name')
                                    ->label('Tên Chapter')
                                    ->default('BNI KINHBAC CHAPTER')
                                    ->placeholder('VD: BNI Power Chapter - HN2'),
                                Toggle::make('is_public')
                                    ->label('Cho phép hiển thị public')
                                    ->default(true)
                                    ->inline(false),

                                SpatieMediaLibraryFileUpload::make('banner')
                                    ->collection('banner')
                                    ->label('Ảnh Banner (Bìa trên cùng)')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '9:16',
                                    ])
                                    ->helperText('Ảnh sẽ được xử lý theo tỷ lệ dọc 9:16 khi chỉnh sửa.')
                                    ->required()
                                    ->minFiles(1)
                                    ->maxFiles(1)
                                    ->panelLayout('grid')
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('avatar')
                                    ->collection('avatar')
                                    ->label('Ảnh chân dung')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '9:16',
                                    ])
                                    ->helperText('Ảnh sẽ được xử lý theo tỷ lệ dọc 9:16 khi chỉnh sửa.')
                                    ->maxFiles(1)
                                    ->panelLayout('grid'),
                                SpatieMediaLibraryFileUpload::make('personal_photos')
                                    ->collection('personal_photos')
                                    ->label('Album Ảnh cá nhân')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                // Thông tin tạo tài khoản User
                                TextInput::make('user_email')
                                    ->label('Email đăng nhập')
                                    ->email()
                                    ->required()
                                    ->helperText('Hệ thống sẽ tự tạo tài khoản với mật khẩu mặc định: kinhbac123'),

                                TextInput::make('phone_cv')->tel()->label('SĐT công việc'),
                                TextInput::make('phone_personal')->tel()->label('SĐT cá nhân'),
                                TextInput::make('email_cv')->email()->label('Email công việc'),
                                TextInput::make('email_personal')->email()->label('Email cá nhân'),

                                TextInput::make('date_of_birth')->label('Ngày sinh'),
                                TextInput::make('place_of_birth')->label('Nơi sinh'),
                                TextInput::make('address_1')->label('Địa chỉ 1'),
                                TextInput::make('address_2')->label('Địa chỉ 2'),

                                RichEditor::make('family_info')->label('Thông tin gia đình')->columnSpanFull(),
                                RichEditor::make('burning_desire')->label('Khát vọng cháy bỏng')->columnSpanFull(),
                                RichEditor::make('unknown_fact')->label('Điều chưa ai biết về tôi')->columnSpanFull(),
                                RichEditor::make('success_key')->label('Chìa khóa thành công')->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('Doanh nghiệp & Sản phẩm')
                            ->schema([
                                TextInput::make('company_name')->label('Tên công ty')->columnSpanFull(),
                                TextInput::make('job_title')->label('Chức danh'),
                                TextInput::make('business_category')->label('Lĩnh vực'),
                                TextInput::make('experience_years')->label('Kinh nghiệm'),
                                RichEditor::make('qualifications')->label('Bằng cấp / Chứng chỉ')->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('business_photos')
                                    ->collection('business_photos')
                                    ->label('Album Ảnh Doanh nghiệp')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                RichEditor::make('core_products')->label('Sản phẩm chính')->columnSpanFull(),
                                RichEditor::make('accompanying_services')->label('Dịch vụ đi kèm')->columnSpanFull(),
                                RichEditor::make('highlight_products')->label('Sản phẩm nổi bật')->columnSpanFull(),

                                SpatieMediaLibraryFileUpload::make('product_gallery_1')
                                    ->collection('product_gallery_1')
                                    ->label('Gallery Sản phẩm 1')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('product_gallery_2')
                                    ->collection('product_gallery_2')
                                    ->label('Gallery Sản phẩm 2')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('product_gallery_3')
                                    ->collection('product_gallery_3')
                                    ->label('Gallery Sản phẩm 3')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('Bảng GAINS')
                            ->schema([
                                RichEditor::make('g_goals')->label('Goals – Mục tiêu')->columnSpanFull(),
                                RichEditor::make('a_accomplishments')->label('Accomplishments – Thành tựu')->columnSpanFull(),
                                RichEditor::make('i_interests')->label('Interests – Sở thích')->columnSpanFull(),
                                RichEditor::make('n_networks')->label('Networks – Mạng lưới kết nối')->columnSpanFull(),
                                RichEditor::make('s_skills')->label('Skills – Kỹ năng')->columnSpanFull(),
                            ]),

                        Tab::make('Cẩm nang Referral')
                            ->schema([
                                RichEditor::make('ideal_referral')->label('Referral lý tưởng')->columnSpanFull(),
                                RichEditor::make('connection_wishes')->label('Mong muốn được giới thiệu')->columnSpanFull(),
                                RichEditor::make('bni_commitment')->label('Cam kết trong BNI')->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('activity_photos')
                                    ->collection('activity_photos')
                                    ->label('Ảnh hoạt động kết nối')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->columnSpanFull(),

                                RichEditor::make('product_description')->label('1. Mô tả sản phẩm/dịch vụ')->columnSpanFull(),
                                RichEditor::make('competitive_advantage')->label('2. Điểm khác biệt')->columnSpanFull(),
                                RichEditor::make('target_market')->label('3. Khách hàng/Thị trường mục tiêu')->columnSpanFull(),
                                RichEditor::make('connection_fields')->label('4. Ngành nghề kết hợp')->columnSpanFull(),
                                RichEditor::make('conversation_starters')->label('5. Khơi gợi nhu cầu')->columnSpanFull(),
                                RichEditor::make('trigger_phrases')->label('6. Lời giới thiệu tốt là gì?')->columnSpanFull(),
                                RichEditor::make('good_referral')->label('7. Referral tốt là ai?')->columnSpanFull(),
                                RichEditor::make('bad_referral')->label('8. Referral không phù hợp')->columnSpanFull(),
                                RichEditor::make('misconceptions')->label('9. Quan niệm sai/Xử lý từ chối')->columnSpanFull(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
