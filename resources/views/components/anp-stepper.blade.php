@props(['currentStep'])

<style>
    .wizard-stepper {
        display: flex;
        justify-content: space-around;
        padding: 0;
        margin-bottom: 2rem;
        list-style: none;
        position: relative;
    }
    .wizard-stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 5%;
        right: 5%;
        height: 2px;
        background-color: #e9ecef;
        z-index: 0;
    }
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        z-index: 1;
        width: 25%;
    }
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .step-title {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    .step.active .step-icon {
        background-color: #e33674; /* Warna pink dari tema Anda */
        color: #fff;
        border-color: #e33674;
    }
    .step.completed .step-icon {
        background-color: #4CAF50; /* Warna hijau untuk selesai */
        color: #fff;
        border-color: #4CAF50;
    }
    .step.active .step-title, .step.completed .step-title {
        font-weight: bold;
        color: #344767;
    }
</style>

<ul class="wizard-stepper">
    <li class="step {{ $currentStep >= 1 ? ($currentStep == 1 ? 'active' : 'completed') : '' }}">
        <div class="step-icon"><i class="material-icons">description</i></div>
        <div class="step-title">1. Inisiasi</div>
    </li>
    <li class="step {{ $currentStep >= 2 ? ($currentStep == 2 ? 'active' : 'completed') : '' }}">
        <div class="step-icon"><i class="material-icons">hub</i></div>
        <div class="step-title">2. Jaringan</div>
    </li>
    <li class="step {{ $currentStep >= 3 ? ($currentStep == 3 ? 'active' : 'completed') : '' }}">
        <div class="step-icon"><i class="material-icons">rule</i></div>
        <div class="step-title">3. Perbandingan</div>
    </li>
    <li class="step {{ $currentStep >= 4 ? ($currentStep == 4 ? 'active' : 'completed') : '' }}">
        <div class="step-icon"><i class="material-icons">emoji_events</i></div>
        <div class="step-title">4. Hasil</div>
    </li>
</ul>