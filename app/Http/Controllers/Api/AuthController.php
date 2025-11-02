<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * body: { username, password }
     */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $v->errors(),
            ], 422);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // Try mahasiswa first (nim or username)
        $mahasiswa = Mahasiswa::where('nim', $username)
            ->orWhere('username', $username)
            ->first();

        if ($mahasiswa && Hash::check($password, $mahasiswa->password)) {
            $token = $mahasiswa->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil (mahasiswa)',
                'data' => [
                    'user' => $mahasiswa,
                    'type' => 'mahasiswa',
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        }

        // Try dosen (nidn or username)
        $dosen = Dosen::where('nidn', $username)
            ->orWhere('username', $username)
            ->first();

        if ($dosen && Hash::check($password, $dosen->password)) {
            $token = $dosen->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil (dosen)',
                'data' => [
                    'user' => $dosen,
                    'type' => 'dosen',
                    'role' => $dosen->role ?? null,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau password tidak cocok.',
        ], 401);
    }

    /**
     * POST /api/register
     * body: { nim, nama, username, email, program_studi_id, password, password_confirmation }
     * Creates mahasiswa and returns token.
     */
    public function registerMahasiswa(Request $request)
    {
        $v = Validator::make($request->all(), [
            'nim' => 'required|string|unique:mahasiswa,nim',
            'nama' => 'required|string',
            'username' => 'required|string|unique:mahasiswa,username',
            'email' => 'required|email|unique:mahasiswa,email',
            'program_studi_id' => 'required|exists:program_studi,id',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $v->errors(),
            ], 422);
        }

        $mahasiswa = Mahasiswa::create([
            'nim' => $request->nim,
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'foto' => null,
            'program_studi_id' => $request->program_studi_id,
        ]);

        $token = $mahasiswa->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => $mahasiswa,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * POST /api/logout
     * Header: Authorization: Bearer <token>
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ]);
    }

    /**
     * POST /api/cek-user
     * body: { user_input }
     * Returns basic user data and type (mahasiswa|dosen) if found.
     */
    public function cekUserInput(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_input' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $v->errors(),
            ], 422);
        }

        $input = $request->input('user_input');

        $mahasiswa = Mahasiswa::where('nim', $input)
            ->orWhere('username', $input)
            ->orWhere('nama', $input)
            ->first();

        if ($mahasiswa) {
            return response()->json([
                'success' => true,
                'message' => 'Akun mahasiswa ditemukan',
                'data' => [
                    'type' => 'mahasiswa',
                    'user' => [
                        'id' => $mahasiswa->id,
                        'nim' => $mahasiswa->nim,
                        'username' => $mahasiswa->username,
                        'nama' => $mahasiswa->nama,
                        'email' => $mahasiswa->email,
                    ],
                ],
            ]);
        }

        $dosen = Dosen::where('nidn', $input)
            ->orWhere('username', $input)
            ->orWhere('nama', $input)
            ->first();

        if ($dosen) {
            return response()->json([
                'success' => true,
                'message' => 'Akun dosen ditemukan',
                'data' => [
                    'type' => 'dosen',
                    'user' => [
                        'id' => $dosen->id,
                        'nidn' => $dosen->nidn,
                        'username' => $dosen->username,
                        'nama' => $dosen->nama,
                        'email' => $dosen->email,
                    ],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
        ], 404);
    }

    /**
     * POST /api/simpan-password
     * body: { user_type, id, password_input, password_input_confirmation }
     */
    public function simpanPassword(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_type' => 'required|in:mahasiswa,dosen',
            'id' => 'required|integer',
            'password_input' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'password_input.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_input.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $v->errors(),
            ], 422);
        }

        $userType = $request->input('user_type');
        $id = $request->input('id');
        $passwordBaru = $request->input('password_input');

        if ($userType === 'mahasiswa') {
            $user = Mahasiswa::find($id);
        } else {
            $user = Dosen::find($id);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->password = Hash::make($passwordBaru);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    /**
     * POST /api/forgot-password
     * body: { user_input }
     * Minimal implementation: check user exists and return 200. (Integrate mail flow if needed)
     */
    public function forgotPassword(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_input' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $v->errors(),
            ], 422);
        }

        $input = $request->input('user_input');

        $mahasiswa = Mahasiswa::where('nim', $input)
            ->orWhere('username', $input)
            ->orWhere('email', $input)
            ->first();

        $dosen = Dosen::where('nidn', $input)
            ->orWhere('username', $input)
            ->orWhere('email', $input)
            ->first();

        if (!$mahasiswa && !$dosen) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        // TODO: Integrate with Laravel notifications / password reset if you want email with token.
        return response()->json([
            'success' => true,
            'message' => 'Akun ditemukan. Lanjutkan proses ganti password (implementasikan email/reset token bila perlu).',
        ]);
    }
}