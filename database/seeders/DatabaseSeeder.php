<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Password1'),
            'role' => 'admin',
            'status' => 'active',
            'country' => 'Tanzania',
            'learning_level' => 'advanced',
        ]);

        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@gmail.com',
            'password' => Hash::make('Password1'),
            'role' => 'student',
            'status' => 'active',
            'country' => 'USA',
            'learning_level' => 'beginner',
        ]);

        $beginnerCourse = Course::create([
            'title' => 'Swahili for Beginners',
            'description' => 'Learn Kiswahili from scratch with greetings, numbers, and everyday phrases. Perfect for foreigners visiting East Africa.',
            'level' => 'beginner',
            'topic' => 'Greetings',
            'status' => 'published',
        ]);

        $intermediateCourse = Course::create([
            'title' => 'Intermediate Swahili',
            'description' => 'Build on your basics with more complex conversations and vocabulary for daily life.',
            'level' => 'intermediate',
            'topic' => 'Conversation',
            'status' => 'published',
        ]);

        $greetings = Lesson::create([
            'course_id' => $beginnerCourse->id,
            'title' => 'Greetings',
            'content' => "Welcome to Swahili Greetings!\n\nIn East Africa, greetings are very important. Start with Jambo (Hello) and Habari yako? (How are you?).\n\nCommon responses include Nzuri (Fine) and Asante (Thank you).",
            'lesson_order' => 1,
            'status' => 'published',
            'quiz_pass_mark' => 70,
            'quiz_time_limit' => 10,
            'quiz_allow_retake' => true,
            'quiz_max_attempts' => 3,
        ]);

        $numbers = Lesson::create([
            'course_id' => $beginnerCourse->id,
            'title' => 'Numbers',
            'content' => "Swahili Numbers 1-5\n\nLearn to count: Moja (1), Mbili (2), Tatu (3), Nne (4), Tano (5).",
            'lesson_order' => 2,
            'status' => 'published',
            'quiz_pass_mark' => 70,
            'quiz_allow_retake' => true,
            'quiz_max_attempts' => 2,
        ]);

        $phrases = Lesson::create([
            'course_id' => $beginnerCourse->id,
            'title' => 'Common Phrases',
            'content' => "Everyday Phrases\n\nUseful phrases for travelers: Karibu (Welcome), Tafadhali (Please), Pole (Sorry).",
            'lesson_order' => 3,
            'status' => 'published',
            'quiz_pass_mark' => 60,
            'quiz_allow_retake' => false,
            'quiz_max_attempts' => 1,
        ]);

        $greetingCards = [
            ['swahili_word' => 'Jambo', 'english_meaning' => 'Hello', 'pronunciation' => 'JAM-bo'],
            ['swahili_word' => 'Habari', 'english_meaning' => 'How are you', 'pronunciation' => 'hah-BAH-ree'],
            ['swahili_word' => 'Asante', 'english_meaning' => 'Thank you', 'pronunciation' => 'ah-SAHN-teh'],
            ['swahili_word' => 'Karibu', 'english_meaning' => 'Welcome', 'pronunciation' => 'kah-REE-boo'],
            ['swahili_word' => 'Kwaheri', 'english_meaning' => 'Goodbye', 'pronunciation' => 'kwah-HEH-ree'],
        ];

        foreach ($greetingCards as $card) {
            Flashcard::create([...$card, 'lesson_id' => $greetings->id]);
        }

        $numberCards = [
            ['swahili_word' => 'Moja', 'english_meaning' => 'One', 'pronunciation' => 'MOH-jah'],
            ['swahili_word' => 'Mbili', 'english_meaning' => 'Two', 'pronunciation' => 'MBEE-lee'],
            ['swahili_word' => 'Tatu', 'english_meaning' => 'Three', 'pronunciation' => 'TAH-too'],
            ['swahili_word' => 'Nne', 'english_meaning' => 'Four', 'pronunciation' => 'N-neh'],
            ['swahili_word' => 'Tano', 'english_meaning' => 'Five', 'pronunciation' => 'TAH-no'],
        ];

        foreach ($numberCards as $card) {
            Flashcard::create([...$card, 'lesson_id' => $numbers->id]);
        }

        Quiz::create([
            'lesson_id' => $greetings->id,
            'question' => 'What does "Jambo" mean?',
            'option_a' => 'Goodbye',
            'option_b' => 'Hello',
            'option_c' => 'Thank you',
            'option_d' => 'Welcome',
            'correct_answer' => 'b',
            'time_limit_seconds' => 30,
            'sort_order' => 1,
        ]);

        Quiz::create([
            'lesson_id' => $greetings->id,
            'question' => 'How do you say "Thank you" in Swahili?',
            'option_a' => 'Karibu',
            'option_b' => 'Habari',
            'option_c' => 'Asante',
            'option_d' => 'Kwaheri',
            'correct_answer' => 'c',
            'time_limit_seconds' => 30,
            'sort_order' => 2,
        ]);

        Quiz::create([
            'lesson_id' => $numbers->id,
            'question' => 'What is "Mbili" in English?',
            'option_a' => 'One',
            'option_b' => 'Two',
            'option_c' => 'Three',
            'option_d' => 'Four',
            'correct_answer' => 'b',
            'time_limit_seconds' => 30,
            'sort_order' => 1,
        ]);

        Quiz::create([
            'lesson_id' => $phrases->id,
            'question' => 'What does "Tafadhali" mean?',
            'option_a' => 'Sorry',
            'option_b' => 'Please',
            'option_c' => 'Welcome',
            'option_d' => 'Goodbye',
            'correct_answer' => 'b',
            'time_limit_seconds' => 40,
            'sort_order' => 1,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $beginnerCourse->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }
}
