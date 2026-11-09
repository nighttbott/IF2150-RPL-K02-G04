{{-- Label status satu baris riwayat --}}
@if ($status === 'berhasil')
    <span class="label label-berhasil">Berhasil</span>
@elseif ($status === 'perlu_diulang')
    <span class="label label-gagal">Perlu diulang</span>
@else
    <span class="label label-netral">Selesai</span>
@endif
