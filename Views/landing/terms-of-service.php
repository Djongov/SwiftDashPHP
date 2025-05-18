<?php

declare(strict_types=1);
?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">
        <?= translate('terms_of_service_heading') ?>
    </h1>
    <p class="text-sm text-gray-500 mb-6">
        <?= str_replace('{date}', date('F j, Y', 1747503381), translate('terms_of_service_subtext')) ?>
    </p>

    <p class="mb-6">
        <?= str_replace(
            ['{site_title}', '{domain}'],
            [SITE_TITLE, 'https://' . $_SERVER['HTTP_HOST']],
            translate('terms_of_service_content')
        ) ?>
    </p>

    <!-- Section: Description -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_description_heading') ?>
    </h2>
    <p class="mb-6">
        <?= translate('terms_of_service_description') ?>
    </p>

    <!-- Section: Use of Service -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_use_of_service_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('terms_of_service_use_of_service') ?>
    </p>
    <ul class="list-disc list-inside mb-6 text-gray-800">
        <li class="ml-4 dark:text-gray-200"><?= translate('terms_of_service_use_of_service_list_1') ?></li>
        <li class="ml-4 dark:text-gray-200"><?= translate('terms_of_service_use_of_service_list_2') ?></li>
        <li class="ml-4 dark:text-gray-200"><?= translate('terms_of_service_use_of_service_list_3') ?></li>
    </ul>

    <!-- Section: Accuracy of Information -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_accuracy_of_information_heading') ?>
    </h2>
    <p class="mb-6">
        <?= translate('terms_of_service_accuracy_of_information') ?>
    </p>

    <!-- Section: No Affiliation -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_no_affiliation_heading') ?>
    </h2>
    <p class="mb-6">
        <?= translate('terms_of_service_no_affiliation') ?>
    </p>

    <!-- Section: Intellectual Property -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_intellectual_property_heading') ?>
    </h2>
    <p class="mb-6">
        <?= str_replace('{site_title}', SITE_TITLE, translate('terms_of_service_intellectual_property')) ?>
    </p>

    <!-- Section: Changes -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_changes_to_terms_heading') ?>
    </h2>
    <p class="mb-6">
        <?= translate('terms_of_service_changes_to_terms') ?>
    </p>

    <!-- Section: Termination -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_termination_heading') ?>
    </h2>
    <p class="mb-6">
        <?= translate('terms_of_service_termination') ?>
    </p>

    <!-- Section: Contact -->
    <h2 class="text-xl font-semibold mt-8 mb-2">
        <?= translate('terms_of_service_contact_us_heading') ?>
    </h2>
    <p class="mb-6">
        <?= str_replace('{email}', 'admin@gamerz-bg.com', translate('terms_of_service_contact_us')) ?>
    </p>
</div>
