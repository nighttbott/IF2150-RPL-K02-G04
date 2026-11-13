<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** Tamu yang membuka halaman utama diarahkan ke halaman Masuk (I-02). */
    public function test_tamu_di_halaman_utama_diarahkan_ke_masuk(): void
    {
        $this->get('/')->assertRedirect(route('masuk'));
        $this->get('/masuk')->assertOk();
    }
}
