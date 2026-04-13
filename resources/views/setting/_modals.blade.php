@php
	$modalViews = [
		'Evaluasi' => 'setting.modals.evaluasi',
		'ProdiUser' => 'setting.modals.prodiuser',
		'KontrakMk' => 'setting.modals.kontrakmk',
		'Mahasiswa' => 'setting.modals.mahasiswa',
		'Permission' => 'setting.modals.permission',
		'Prodi' => 'setting.modals.prodi',
		'Role' => 'setting.modals.role',
		'Semester' => 'setting.modals.semester',
		'User' => 'setting.modals.user',
	];
@endphp

@foreach ($modalViews as $modalTitle => $modalView)
	@includeWhen(($title ?? '') === $modalTitle, $modalView)
@endforeach
