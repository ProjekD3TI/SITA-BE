<?php

namespace Tests\Unit\TugasAkhir;

use App\Models\StatusForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bisa_membuat_status_form()
    {
        // Test pembuatan instance status form
        $statusForm = StatusForm::factory()->create();

        $this->assertInstanceOf(StatusForm::class, $statusForm);
        $this->assertDatabaseHas('status_form', ['id' => $statusForm->id]);
    }

    /** @test */
    public function memiliki_kolom_fillable()
    {
        // Test data dapat diisi sesuai dengan fillable
        $statusForm = new StatusForm([
            'nama_form' => 'Form Pendaftaran TA',
            'is_open' => true,
        ]);

        $this->assertEquals('Form Pendaftaran TA', $statusForm->nama_form);
        $this->assertEquals(true, $statusForm->is_open);
    }

    /** @test */
    public function menggunakan_table_status_form()
    {
        // Test model menggunakan table yang benar
        $statusForm = new StatusForm();

        $this->assertEquals('status_form', $statusForm->getTable());
    }
}
