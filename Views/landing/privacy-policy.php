<div class="max-w-4xl mx-auto px-4 py-8 text-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-2">
        <?= translate('privacy_policy_heading') ?>
    </h1>
    <p class="text-sm text-gray-500 mb-6">
        <?= str_replace('{date}', date('F j, Y', 1752003381), translate('privacy_policy_subtext')) ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_intro_heading') ?>
    </h2>
    <p class="mb-4">
        <?= str_replace('{site_title}', SITE_TITLE, translate('privacy_policy_intro')) ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_data_collection_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_data_collection') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_cookies_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_cookies') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_third_party_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_third_party') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_data_use_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_data_use') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_data_sharing_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_data_sharing') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_security_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_security') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_user_rights_heading') ?>
    </h2>
    <p class="mb-4">
        <?= str_replace('{email}', ADMINISTRATOR_EMAIL, translate('privacy_policy_user_rights')) ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_changes_heading') ?>
    </h2>
    <p class="mb-4">
        <?= translate('privacy_policy_changes') ?>
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">
        <?= translate('privacy_policy_contact_heading') ?>
    </h2>
    <p class="mb-4">
        <?= str_replace('{email}', ADMINISTRATOR_EMAIL, translate('privacy_policy_contact')) ?>
    </p>
</div>
