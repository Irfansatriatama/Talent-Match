<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;

class ProgrammingQuestionsSeeder extends Seeder
{
    public function run()
    {
        $testId = 1;
        
        // Soal 1: Variables dan Data Types
        $q1 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Apa output dari kode berikut?
x = 5
y = "3"
z = x + int(y)
print(z)',
            'question_order' => 1,
            'programming_category' => 'Variables dan Data Types' //
        ]);
        
        QuestionOption::insert([
            ['question_id' => $q1->question_id, 'option_text' => '8', 'is_correct_programming' => true, 'display_order' => 1], //
            ['question_id' => $q1->question_id, 'option_text' => '53', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q1->question_id, 'option_text' => 'Error', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q1->question_id, 'option_text' => '5.3', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 2: Control Structures - If/Else
        $q2 = Question::create([
            'test_id' => $testId,
            'question_text' => 'def check_grade(score):
    if score >= 80:
        return "A"
    elif score >= 70:
        return "B"
    elif score >= 60:
        return "C"
    else:
        return "F"

result = check_grade(75)
print(result)',
            'question_order' => 2,
            'programming_category' => 'Control Structures' //
        ]);
        
        QuestionOption::insert([
            ['question_id' => $q2->question_id, 'option_text' => 'A', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q2->question_id, 'option_text' => 'B', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q2->question_id, 'option_text' => 'C', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q2->question_id, 'option_text' => 'F', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 3: Loops - For Loop
        $q3 = Question::create([
            'test_id' => $testId,
            'question_text' => 'total = 0
for i in range(1, 4):
    total = total + (i * 2)
print(total)',
            'question_order' => 3,
            'programming_category' => 'Loops' //
        ]);
        
        QuestionOption::insert([
            ['question_id' => $q3->question_id, 'option_text' => '6', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q3->question_id, 'option_text' => '8', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q3->question_id, 'option_text' => '12', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q3->question_id, 'option_text' => '16', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 4: Arrays
        $q4 = Question::create([
            'test_id' => $testId,
            'question_text' => 'numbers = [1, 2, 3, 4, 5]
numbers[2] = 10
result = numbers[1] + numbers[2]
print(result)',
            'question_order' => 4,
            'programming_category' => 'Arrays' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q4->question_id, 'option_text' => '5', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q4->question_id, 'option_text' => '12', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q4->question_id, 'option_text' => '13', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q4->question_id, 'option_text' => 'Error', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 5: Functions
        $q5 = Question::create([
            'test_id' => $testId,
            'question_text' => 'def multiply(a, b):
    return a * b

def calculate():
    x = 3
    y = 4
    return multiply(x, y) + 2

result = calculate()
print(result)',
            'question_order' => 5,
            'programming_category' => 'Functions' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q5->question_id, 'option_text' => '12', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q5->question_id, 'option_text' => '14', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q5->question_id, 'option_text' => '9', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q5->question_id, 'option_text' => '11', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 6: Code Reading
        $q6 = Question::create([
            'test_id' => $testId,
            'question_text' => 'count = 0
for i in range(2):
    for j in range(3):
        count = count + 1
print(count)',
            'question_order' => 6,
            'programming_category' => 'Code Reading' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q6->question_id, 'option_text' => '2', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q6->question_id, 'option_text' => '3', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q6->question_id, 'option_text' => '5', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q6->question_id, 'option_text' => '6', 'is_correct_programming' => true, 'display_order' => 4], //
        ]);

        // Soal 7: Debugging
        $q7 = Question::create([
            'test_id' => $testId,
            'question_text' => 'def find_maximum(numbers):
    max_val = 0
    for num in numbers:
        if num > max_val:
            max_val = num # Perbaikan dari \'max_val\' menjadi \'max_val = num\'
    return max_val

# Test: find_maximum([-5, -2, -8, -1])
Apa masalah utama pada kode di atas?', //
            'question_order' => 7,
            'programming_category' => 'Debugging' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q7->question_id, 'option_text' => 'Loop tidak efisien', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q7->question_id, 'option_text' => 'Inisialisasi max_val salah untuk bilangan negatif', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q7->question_id, 'option_text' => 'Kondisi if statement salah', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q7->question_id, 'option_text' => 'Return statement di posisi yang salah', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 8: Arrays
        $q8 = Question::create([
            'test_id' => $testId,
            'question_text' => 'data = [10, 20, 30, 40]
index = len(data) - 1
result = data[index] + data[0]
print(result)',
            'question_order' => 8,
            'programming_category' => 'Arrays' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q8->question_id, 'option_text' => '30', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q8->question_id, 'option_text' => '40', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q8->question_id, 'option_text' => '50', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q8->question_id, 'option_text' => '60', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 9: Pattern Recognition
        $q9 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Diberikan sequence: 1, 4, 9, 16, 25, ?
Angka selanjutnya adalah:', //
            'question_order' => 9,
            'programming_category' => 'Pattern Recognition' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q9->question_id, 'option_text' => '30', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q9->question_id, 'option_text' => '36', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q9->question_id, 'option_text' => '49', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q9->question_id, 'option_text' => '64', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 10: Algorithm Design
        $q10 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Untuk mengurutkan array [64, 25, 12, 22, 11] secara ascending, manakah strategi yang paling efisien?', //
            'question_order' => 10,
            'programming_category' => 'Algorithm Design' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q10->question_id, 'option_text' => 'Bandingkan semua elemen satu per satu', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q10->question_id, 'option_text' => 'Bagi array menjadi bagian kecil, urutkan, lalu gabungkan', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q10->question_id, 'option_text' => 'Acak urutan sampai terurut', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q10->question_id, 'option_text' => 'Tukar elemen yang berdekatan berulang kali', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 11: Decomposition
        $q11 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Untuk membuat sistem login, komponen apa yang TIDAK diperlukan?', //
            'question_order' => 11,
            'programming_category' => 'Decomposition' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q11->question_id, 'option_text' => 'Validasi input username/password', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q11->question_id, 'option_text' => 'Enkripsi password', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q11->question_id, 'option_text' => 'Session management', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q11->question_id, 'option_text' => 'Database untuk menyimpan gambar profil', 'is_correct_programming' => true, 'display_order' => 4], //
        ]);

        // Soal 12: Abstraction
        $q12 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Dalam merancang class "Vehicle", atribut mana yang paling essential?', //
            'question_order' => 12,
            'programming_category' => 'Abstraction' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q12->question_id, 'option_text' => 'Color, Brand, Price', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q12->question_id, 'option_text' => 'Speed, Fuel, Engine', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q12->question_id, 'option_text' => 'Owner, Insurance, Registration', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q12->question_id, 'option_text' => 'Manufacturing Date, Warranty, Dealer', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 13: Logic
        $q13 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Jika semua programmer suka kopi DAN beberapa programmer suka teh, manakah pernyataan yang PASTI benar?', //
            'question_order' => 13,
            'programming_category' => 'Logic' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q13->question_id, 'option_text' => 'Semua programmer suka teh', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q13->question_id, 'option_text' => 'Tidak ada programmer yang suka teh', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q13->question_id, 'option_text' => 'Ada programmer yang suka kopi dan teh', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q13->question_id, 'option_text' => 'Semua yang suka kopi adalah programmer', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 14: Algorithm Efficiency
        $q14 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Untuk mencari elemen dalam array terurut dengan 1000 elemen, pendekatan mana yang paling efisien?', //
            'question_order' => 14,
            'programming_category' => 'Algorithm Efficiency' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q14->question_id, 'option_text' => 'Linear search dari awal', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q14->question_id, 'option_text' => 'Linear search dari tengah', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q14->question_id, 'option_text' => 'Binary search', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q14->question_id, 'option_text' => 'Random search', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 15: Class dan Object
        $q15 = Question::create([
            'test_id' => $testId,
            'question_text' => 'class Student:
    def __init__(self, name, age):
        self.name = name
        self.age = age # Perbaikan dari \'self.age age\'
    def get_info(self):
        return self.name + " is " + str(self.age) # Perbaikan dari \'return self.name + " is " + " " + "10" + " " + str(self.age)\'

student1 = Student("Alice", 20)
print(student1.get_info())
Output yang dihasilkan:', //
            'question_order' => 15,
            'programming_category' => 'Class dan Object' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q15->question_id, 'option_text' => 'Alice is 20', 'is_correct_programming' => true, 'display_order' => 1], //
            ['question_id' => $q15->question_id, 'option_text' => 'Student Alice 20', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q15->question_id, 'option_text' => 'name is age', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q15->question_id, 'option_text' => 'Error', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 16: Inheritance
        $q16 = Question::create([
            'test_id' => $testId,
            'question_text' => 'class Animal:
    def sound(self):
        return "Some sound"

class Dog(Animal):
    def sound(self):
        return "Bark"

my_dog = Dog()
print(my_dog.sound())', //
            'question_order' => 16,
            'programming_category' => 'Inheritance' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q16->question_id, 'option_text' => 'Some sound', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q16->question_id, 'option_text' => 'Bark', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q16->question_id, 'option_text' => 'Animal sound', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q16->question_id, 'option_text' => 'Error', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 17: Encapsulation
        $q17 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Dalam OOP, mengapa menggunakan private attributes (seperti _name)?', //
            'question_order' => 17,
            'programming_category' => 'Encapsulation' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q17->question_id, 'option_text' => 'Untuk menghemat memory', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q17->question_id, 'option_text' => 'Untuk meningkatkan kecepatan program', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q17->question_id, 'option_text' => 'Untuk melindungi data dari akses langsung', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q17->question_id, 'option_text' => 'Untuk membuat kode lebih pendek', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 18: Stack Operations
        $q18 = Question::create([
            'test_id' => $testId,
            'question_text' => 'stack = []
stack.append(1)
stack.append(2)
stack.append(3)
x = stack.pop()
y = stack.pop()
result = x + y
print(result)', //
            'question_order' => 18,
            'programming_category' => 'Stack Operations' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q18->question_id, 'option_text' => '3', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q18->question_id, 'option_text' => '4', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q18->question_id, 'option_text' => '5', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q18->question_id, 'option_text' => '6', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 19: Time Complexity
        $q19 = Question::create([
            'test_id' => $testId,
            'question_text' => 'def find_pair(arr, target):
    for i in range(len(arr)):
        for j in range(i + 1, len(arr)): # Perbaikan dari range(1+1, len(arr)) menjadi range(i + 1, len(arr))
            if arr[i] + arr[j] == target: # Perbaikan dari arr[1] + arr menjadi arr[i] + arr[j]
                return True
    return False
Time complexity dari algoritma di atas adalah:', //
            'question_order' => 19,
            'programming_category' => 'Time Complexity' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q19->question_id, 'option_text' => 'O(n)', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q19->question_id, 'option_text' => 'O(n log n)', 'is_correct_programming' => false, 'display_order' => 2], //
            ['question_id' => $q19->question_id, 'option_text' => 'O(n^2)', 'is_correct_programming' => true, 'display_order' => 3], //
            ['question_id' => $q19->question_id, 'option_text' => 'O(2^n)', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);

        // Soal 20: Binary Search Tree
        $q20 = Question::create([
            'test_id' => $testId,
            'question_text' => 'Dalam Binary Search Tree, jika root node bernilai 50, di mana posisi yang tepat untuk menyimpan nilai 25?', //
            'question_order' => 20,
            'programming_category' => 'Binary Search Tree' //
        ]);
        QuestionOption::insert([
            ['question_id' => $q20->question_id, 'option_text' => 'Di sebelah kanan root', 'is_correct_programming' => false, 'display_order' => 1], //
            ['question_id' => $q20->question_id, 'option_text' => 'Di sebelah kiri root', 'is_correct_programming' => true, 'display_order' => 2], //
            ['question_id' => $q20->question_id, 'option_text' => 'Sebagai child dari node terkecil', 'is_correct_programming' => false, 'display_order' => 3], //
            ['question_id' => $q20->question_id, 'option_text' => 'Sebagai child dari node terbesar', 'is_correct_programming' => false, 'display_order' => 4], //
        ]);
    }
}