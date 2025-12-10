<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class registerController extends Controller
{
    public function showForm()
    {
        return view('home.registrasi');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'min:6'],
                'role' => ['required', Rule::in(['student', 'teacher', 'murid', 'guru'])],
                'kodeKelas' => ['nullable', 'string', 'max:50'],
                'type_id_other' => ['nullable', Rule::in(['NISN', 'NIM', 'NIP', 'NIDN', 'NUPTK'])],
                'id_other' => ['nullable', 'string', 'max:255'],
            ]);


            $roleMap = [
                'murid' => 'student',
                'student' => 'student',
                'guru' => 'teacher',
                'teacher' => 'teacher'
            ];
            $role = $roleMap[strtolower($validated['role'])];

            $kodeKelas = null;
            if (array_key_exists('kodeKelas', $validated) && !is_null($validated['kodeKelas'])) {
                $temp = trim($validated['kodeKelas']);
                if ($temp !== '' && strtolower($temp) !== 'null') {
                    $kodeKelas = $temp;
                }
            }

            // Extract optional identity fields (accept empty -> set null)
            $typeIdOther = $validated['type_id_other'] ?? null;
            $idOther = isset($validated['id_other']) && trim($validated['id_other']) !== '' ? trim($validated['id_other']) : null;

            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'type_id_other' => $typeIdOther,
                'id_other' => $idOther,
            ]);


            if ($kodeKelas) {
                $kelas = Classes::whereRaw('LOWER(token) = ?', [strtolower($kodeKelas)])->first();

                if (!$kelas) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Kode kelas tidak ditemukan.', 422);
                }

                if ($role === 'student') {
                    $exists = DB::table('student_classes')->where('id_student', $user->id)->where('id_class', $kelas->id)->exists();
                    if (!$exists) {
                        DB::table('student_classes')->insert(['id_student' => $user->id, 'id_class' => $kelas->id]);
                    }
                } else {
                    $exists = DB::table('teacher_classes')->where('id_teacher', $user->id)->where('id_class', $kelas->id)->exists();
                    if (!$exists) {
                        DB::table('teacher_classes')->insert(['id_teacher' => $user->id, 'id_class' => $kelas->id]);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->isJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil.',
                    'data' => ['user_id' => $user->id, 'role' => $user->role]
                ], 201);
            }

            return redirect('/login')->with('success', 'Registrasi berhasil! Silakan masuk.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Register error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (config('app.debug') && ($request->wantsJson() || $request->isJson() || $request->ajax())) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }

            return $this->errorResponse($request, 'Terjadi kesalahan saat registrasi.', 500);
        }
    }

    protected function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->wantsJson() || $request->isJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }
        return back()->withInput($request->except('password'))->withErrors(['error' => $message]);
    }
}
