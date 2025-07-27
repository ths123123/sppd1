<?php
namespace Database\Factories;
use App\Models\TemplateDokumen;
use Illuminate\Database\Eloquent\Factories\Factory;
class TemplateDokumenFactory extends Factory
{
    protected $model = TemplateDokumen::class;
    public function definition()
    {
        return [
            'nama_template' => $this->faker->word . ' Template',
            'tipe_file' => 'pdf',
            'path_file' => 'dummy.pdf',
            'status_aktif' => true,
            'jenis_template' => 'sppd',
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
