<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\User;
use App\Services\LevelEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function __construct(private LevelEnrollmentService $levelEnrollmentService) {}

    public function index(Request $request)
    {
        $query = User::query()->where('role', 'student');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($level = $request->string('learning_level')->toString()) {
            $query->where('learning_level', $level);
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(StoreStudentRequest $request)
    {
        $student = User::create([
            ...$request->validated(),
            'role' => 'student',
            'password' => Hash::make($request->password),
        ]);
        $this->levelEnrollmentService->enroll($student, $student->learning_level ?: 'beginner');

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function edit(User $student)
    {
        $this->ensureStudent($student);

        return view('admin.students.edit', compact('student'));
    }

    public function update(StoreStudentRequest $request, User $student)
    {
        $this->ensureStudent($student);
        $oldLevel = $student->learning_level;

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $student->update($data);
        if ($oldLevel !== $student->learning_level) {
            $this->levelEnrollmentService->enroll($student, $student->learning_level ?: 'beginner');
        }
        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(User $student)
    {
        $this->ensureStudent($student);

        if ($student->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }

    private function ensureStudent(User $user): void
    {
        abort_unless($user->role === 'student', 404);
    }
}
