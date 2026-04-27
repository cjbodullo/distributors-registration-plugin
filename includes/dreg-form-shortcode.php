<?php
/**
 * Distributor registration form shortcode output.
 */

if (!defined('ABSPATH')) {
    exit;
}

function dreg_render_distributor_registration_form_shortcode($atts, $content = '', $tag = '')
{
    $shortcodeTag = $tag !== '' ? $tag : 'distributor_registration_form';
    $atts = shortcode_atts(
        [
            'thank_you_url' => '',
            'contact_email' => 'info@babybrands.com',
        ],
        $atts,
        $shortcodeTag
    );

    $thankYouUrl = esc_url(trim((string) $atts['thank_you_url']));
    $contactEmail = sanitize_email((string) $atts['contact_email']);
    if ($contactEmail === '') {
        $contactEmail = 'info@babybrands.com';
    }
    $contactEmail = (string) apply_filters('dreg_distributor_registration_contact_email', $contactEmail, $shortcodeTag);
    if (!is_email($contactEmail)) {
        $contactEmail = 'info@babybrands.com';
    }

    $feedback = dreg_get_distributor_registration_feedback();
    $redirectUrl = dreg_get_distributor_registration_redirect_url();
    $successRedirectUrl = $thankYouUrl !== '' ? $thankYouUrl : $redirectUrl;
    $successRedirectUrl = wp_validate_redirect($successRedirectUrl, $redirectUrl);
    $successRedirectUrl = remove_query_arg(['dreg_dr_status', 'dreg_dr_message'], $successRedirectUrl);
    $formPageUrl = remove_query_arg(['dreg_dr_status', 'dreg_dr_message'], $redirectUrl);

    // Local stylesheet only (no vendor CSS / no CDN), same approach as HC registration.
    wp_enqueue_style(
        'dreg-distributor-registration',
        DREG_PLUGIN_URL . 'assets/css/distributor-registration.css',
        [],
        DREG_VERSION
    );

    $isSuccess = $feedback['status'] === 'success';
    $errorMessage = $feedback['status'] === 'error' ? $feedback['message'] : '';

    $provinces = dreg_get_canadian_provinces();

    $assets = [
        'step1' => DREG_PLUGIN_URL . 'assets/images/step1.webp',
        'step2' => DREG_PLUGIN_URL . 'assets/images/step2.webp',
        'step3' => DREG_PLUGIN_URL . 'assets/images/step3.webp',
        'samplits_logo' => DREG_PLUGIN_URL . 'assets/images/samplits-logo.png',
        'thankyou_banner' => DREG_PLUGIN_URL . 'assets/images/distributors-banner-thankyou.webp',
    ];

    ob_start();
    ?>
    <main class="dreg-dr-shell">
        <div class="dreg-dr-container">
            <?php if ($isSuccess) : ?>
                <div class="dreg-dr-thankyou dreg-dr-thankyou-page">
                    <div class="dreg-dr-thankyou-card">
                        <div class="dreg-dr-thankyou-badge"><?php esc_html_e('DISTRIBUTOR APPLICATION RECEIVED', 'distributors-registration'); ?></div>
                        <h1 class="dreg-dr-thankyou-card-title"><?php esc_html_e('Thank You!', 'distributors-registration'); ?></h1>
                        <p class="dreg-dr-thankyou-card-lead"><?php esc_html_e('We have received your request form.', 'distributors-registration'); ?></p>
                        <div class="dreg-dr-thankyou-banner-wrap text-center">
                            <img class="dreg-dr-thankyou-banner" src="<?php echo esc_url($assets['thankyou_banner']); ?>" alt="">
                        </div>
                        <p class="dreg-dr-thankyou-card-text">
                            <?php esc_html_e('We will review your request prior to our next distribution. Once approved you will receive an email from us.', 'distributors-registration'); ?>
                        </p>
                        <p class="dreg-dr-thankyou-card-text">
                            <?php esc_html_e('If you have any questions prior, please reach out to', 'distributors-registration'); ?>
                            <a href="mailto:<?php echo esc_attr($contactEmail); ?>"><?php echo esc_html($contactEmail); ?></a>
                        </p>
                        <a class="btn btn-primary dreg-dr-thankyou-cta" href="<?php echo esc_url($formPageUrl); ?>"><?php esc_html_e('Submit another application', 'distributors-registration'); ?></a>
                        <div class="dreg-dr-thankyou-card-footer">
                            <div class="dreg-dr-powered-by">Powered by Samplits</div>
                            <img src="<?php echo esc_url($assets['samplits_logo']); ?>" alt="Samplits Logo">
                        </div>
                    </div>
                </div>
                <script>
                (function () {
                    try {
                        var u = new URL(window.location.href);
                        if (!u.searchParams.has('dreg_dr_status')) {
                            return;
                        }
                        u.searchParams.delete('dreg_dr_status');
                        u.searchParams.delete('dreg_dr_message');
                        var qs = u.searchParams.toString();
                        var newUrl = u.pathname + (qs ? '?' + qs : '') + u.hash;
                        window.history.replaceState(null, '', newUrl);
                    } catch (e) {}
                })();
                </script>
            <?php elseif ($errorMessage !== '') : ?>
                <div class="alert alert-danger text-center mb-4">
                    <?php echo esc_html($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($feedback['status'] === 'error') : ?>
                <script>
                (function () {
                    try {
                        var u = new URL(window.location.href);
                        if (!u.searchParams.has('dreg_dr_status') && !u.searchParams.has('dreg_dr_message')) {
                            return;
                        }
                        u.searchParams.delete('dreg_dr_status');
                        u.searchParams.delete('dreg_dr_message');
                        var qs = u.searchParams.toString();
                        var newUrl = u.pathname + (qs ? '?' + qs : '') + u.hash;
                        window.history.replaceState(null, '', newUrl);
                    } catch (e) {}
                })();
                </script>
            <?php endif; ?>

            <?php if (!$isSuccess) : ?>
                <div class="dreg-dr-form-container">
                    <div class="p-3">
                        <img class="dreg-dr-header-image" id="dreg-dr-stepImage" src="<?php echo esc_url($assets['step1']); ?>" alt="Distributor application header">
                    </div>

                    <div class="dreg-dr-topbar">
                        <div class="dreg-dr-title">DISTRIBUTOR APPLICATION</div>
                    </div>

                    <div class="dreg-dr-step-indicator">
                        <div class="dreg-dr-step-items">
                            <div class="dreg-dr-step-item">
                                <div class="dreg-dr-step-circle is-active" id="dreg-dr-stepIndicator1">1</div>
                                <div class="dreg-dr-step-label" id="dreg-dr-stepLabel1">General Information</div>
                                <span class="dreg-dr-step-connector" aria-hidden="true"></span>
                            </div>
                            <div class="dreg-dr-step-item">
                                <div class="dreg-dr-step-circle" id="dreg-dr-stepIndicator2">2</div>
                                <div class="dreg-dr-step-label" id="dreg-dr-stepLabel2">Sample Information</div>
                                <span class="dreg-dr-step-connector" aria-hidden="true"></span>
                            </div>
                            <div class="dreg-dr-step-item">
                                <div class="dreg-dr-step-circle" id="dreg-dr-stepIndicator3">3</div>
                                <div class="dreg-dr-step-label" id="dreg-dr-stepLabel3">Terms and Condition</div>
                            </div>
                        </div>
                    </div>

                    <form class="dreg-dr-form px-3 pb-3" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
                            <input type="hidden" name="action" value="dreg_distributor_register">
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirectUrl); ?>">
                            <input type="hidden" name="dreg_dr_success_redirect" value="<?php echo esc_url($successRedirectUrl); ?>">
                            <?php wp_nonce_field('dreg_distributor_register', 'dreg_dr_nonce'); ?>

                            <div class="dreg-dr-steps-anim-wrap">
                            <div class="dreg-dr-step dreg-dr-step-active" id="dreg-dr-step1">
                                <div class="form-group">
                                    <label class="font-weight-bold d-block" for="dreg-dr-name">*Name of the organization</label>
                                    <input type="text" class="form-control" id="dreg-dr-name" name="name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-firstName">*Contact First Name</label>
                                            <input type="text" class="form-control" id="dreg-dr-firstName" name="firstName" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-lastName">*Contact Last Name</label>
                                            <input type="text" class="form-control" id="dreg-dr-lastName" name="lastName" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-email">*Email Address</label>
                                            <input type="email" class="form-control" id="dreg-dr-email" name="email" required>
                                            <div class="invalid-feedback" id="dreg-dr-emailError" style="display:none;">Invalid email address</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-job">*Job Title</label>
                                            <input type="text" class="form-control" id="dreg-dr-job" name="job" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold d-block" for="dreg-dr-department">*Department/Unit</label>
                                    <input type="text" class="form-control" id="dreg-dr-department" name="department" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-phone">*Telephone Number</label>
                                            <input type="text" class="form-control" id="dreg-dr-phone" name="phone" required>
                                            <div class="invalid-feedback" id="dreg-dr-phoneError" style="display:none;">Please enter a valid phone number</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-extension">Extension</label>
                                            <input type="text" class="form-control" id="dreg-dr-extension" name="extension">
                                            <div class="invalid-feedback" id="dreg-dr-extensionError" style="display:none;">Please enter numbers only</div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h3 class="h6 font-weight-bold mb-3">Shipping Information</h3>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-address1">*Address 1</label>
                                            <input type="text" class="form-control" id="dreg-dr-address1" name="address1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-suite">Suite #</label>
                                            <input type="text" class="form-control" id="dreg-dr-suite" name="suite">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-city">*City</label>
                                            <input type="text" class="form-control" id="dreg-dr-city" name="city" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-province">*Province</label>
                                            <select class="form-control" id="dreg-dr-province" name="province" required>
                                                <option value="">Select Province</option>
                                                <?php foreach ($provinces as $abbr => $label) : ?>
                                                    <option value="<?php echo esc_attr($abbr); ?>"><?php echo esc_html($abbr.' - '.$label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold d-block" for="dreg-dr-postalCode">*Postal Code</label>
                                            <input type="text" class="form-control" id="dreg-dr-postalCode" name="postalCode" required>
                                            <div class="invalid-feedback" id="dreg-dr-postalCodeError" style="display:none;">Format: XNX NXN</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="font-weight-bold d-block">*Category</label>
                                    <?php
                                    $categories = [
                                        'Doula / Midwife',
                                        'Ultrasound',
                                        'Prenatal Instructor',
                                        'Hospital',
                                        'Dr. / OBGYN',
                                        'Trade Show',
                                        'Pregnancy Centre',
                                        'Medical Centre',
                                        'Other',
                                    ];
                                    foreach ($categories as $cat) :
                                        $id = 'dreg-dr-category-' . sanitize_title($cat);
                                        ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="category" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($cat); ?>" required>
                                            <label class="form-check-label" for="<?php echo esc_attr($id); ?>"><?php echo esc_html($cat); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold d-block">*Identify the majority of patients you cater to</label>
                                    <?php foreach (['Prenatal', 'Postnatal', 'Both'] as $pt) : ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="patientsType" id="dreg-dr-patientsType-<?php echo esc_attr(strtolower($pt)); ?>" value="<?php echo esc_attr($pt); ?>" required>
                                            <label class="form-check-label" for="dreg-dr-patientsType-<?php echo esc_attr(strtolower($pt)); ?>"><?php echo esc_html($pt); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary dreg-dr-next">Continue</button>
                                </div>
                            </div>

                            <div class="dreg-dr-step" id="dreg-dr-step2">
                                <label class="font-weight-bold">*IF AVAILABLE, do you want any of the following included in your gift bags?</label>
                                <div class="form-group p-2">
                                    <div class="mb-2">Bottles/Pacifiers</div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="bottles" id="dreg-dr-bottlesYes" value="Yes" required>
                                        <label class="form-check-label" for="dreg-dr-bottlesYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="bottles" id="dreg-dr-bottlesNo" value="No" required>
                                        <label class="form-check-label" for="dreg-dr-bottlesNo">No</label>
                                    </div>

                                    <div class="mt-3 mb-2">Vitamins</div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="vitamins" id="dreg-dr-vitaminsYes" value="Yes" required>
                                        <label class="form-check-label" for="dreg-dr-vitaminsYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="vitamins" id="dreg-dr-vitaminsNo" value="No" required>
                                        <label class="form-check-label" for="dreg-dr-vitaminsNo">No</label>
                                    </div>

                                    <div class="mt-3 mb-2">Formula</div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="formula" id="dreg-dr-formulaYes" value="Yes" required>
                                        <label class="form-check-label" for="dreg-dr-formulaYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="formula" id="dreg-dr-formulaNo" value="No" required>
                                        <label class="form-check-label" for="dreg-dr-formulaNo">No</label>
                                    </div>
                                </div>
                                <label class="font-weight-bold d-block mb-2">*Indicate the language in which you want to receive your sample bag</label>
                                    
                                <div class="form-group p-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="language" id="dreg-dr-english" value="1" required>
                                        <label class="form-check-label" for="dreg-dr-english">English</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="language" id="dreg-dr-french" value="2" required>
                                        <label class="form-check-label" for="dreg-dr-french">French</label>
                                    </div>
                                </div>
                                <label class="font-weight-bold">*Please indicate how often you would like to receive samples: (Minimum 50 Per Box)</label>
                                    
                                <div class="form-group p-2">
                                    <?php
                                    $freq = [
                                        'Monthly' => 'Monthly (January - December)',
                                        'BiMonthly' => 'BiMonthly (January, March, May, July, September, November)',
                                        'Quarterly' => 'Quarterly (February, May, August, November)',
                                        'SemiAnnually' => 'Semi-Annually',
                                        'Annual' => 'Annual',
                                    ];
                                    foreach ($freq as $val => $label) :
                                        $id = 'dreg-dr-freq-' . strtolower($val);
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sampleFrequency" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($val); ?>" required>
                                            <label class="form-check-label" for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <label class="font-weight-bold">*Approximately how many patients does your center see monthly</label>
                                    
                                <div class="form-group p-2">
                                    <input type="text" class="form-control" id="dreg-dr-numberOfPatients" name="numberOfPatients" required>
                                    <div class="invalid-feedback" id="dreg-dr-numberOfPatientsError" style="display:none;">Numbers only</div>
                                </div>
                                <label class="font-weight-bold d-block mb-2">*Based on the number of patient visits, how many bags per shipment?</label>
                                    
                                <div class="form-group p-2">
                                    <?php foreach (['50', '100', '150', '200'] as $n) : ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="nuOfSamples" id="dreg-dr-samples-<?php echo esc_attr($n); ?>" value="<?php echo esc_attr($n); ?>" required>
                                            <label class="form-check-label" for="dreg-dr-samples-<?php echo esc_attr($n); ?>"><?php echo esc_html($n); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-primary dreg-dr-prev mr-3">Previous</button>
                                    <button type="button" class="btn btn-primary dreg-dr-next">Continue</button>
                                </div>
                            </div>

                            <div class="dreg-dr-step" id="dreg-dr-step3">
                                <div class="form-group">
                                    <label class="font-weight-bold d-block">Number of Doctors/Doula-Midwife at this centre</label>
                                    <div id="dreg-dr-doctors">
                                        <div class="dreg-dr-doctor-row border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Prefix</label>
                                                        <select class="form-control" name="prefix[]">
                                                            <option value="">No Selection</option>
                                                            <option value="DR.">DR.</option>
                                                            <option value="Doula-Midwife">Doula-Midwife</option>
                                                            <option value="Educator">Educator</option>
                                                            <option value="Sonographer">Sonographer</option>
                                                            <option value="Nurse">Nurse</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>*First Name</label>
                                                        <input type="text" class="form-control" name="fName[]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>*Last Name</label>
                                                        <input type="text" class="form-control" name="lName[]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <div class="form-group w-100">
                                                        <button type="button" class="btn btn-danger dreg-dr-remove-doctor" style="display:none;">&times;</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="text-right">
                                        <a href="#" id="dreg-dr-add-doctor">Add new Doctor</a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">Special Shipping Instructions</label>
                                    <input type="text" class="form-control" id="dreg-dr-specialShippingInstruction" name="specialShippingInstruction">
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="dreg-dr-terms1" name="terms1" value="on" required>
                                        <label class="form-check-label" for="dreg-dr-terms1">
                                            By registering, you will be given free samples in a sealed bag. One bag per patient, to be distributed as received.
                                        </label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="dreg-dr-terms2" name="terms2" value="on" required>
                                        <label class="form-check-label" for="dreg-dr-terms2">
                                            By registering, you will be given brochures for your reception area to help us ship samples directly.
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-primary dreg-dr-prev mr-3">Previous</button>
                                    <button type="submit" class="btn btn-primary">Register Now</button>
                                </div>
                            </div>
                            </div>
                        </form>

                    <div class="dreg-dr-footer p-4 dreg-dr-thankyou-card-footer">
                        <div class="dreg-dr-powered-by">Powered by Samplits</div>
                        <img src="<?php echo esc_url($assets['samplits_logo']); ?>" alt="Samplits Logo">
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var form = document.querySelector('.dreg-dr-form');
                    if (!form || form.dataset.dregReady === '1') return;
                    form.dataset.dregReady = '1';

                    var currentStep = 1;
                    var stepAnimLock = false;
                    var stepAnimMs = 400;
                    var stepImage = document.getElementById('dreg-dr-stepImage');
                    var step1 = document.getElementById('dreg-dr-step1');
                    var step2 = document.getElementById('dreg-dr-step2');
                    var step3 = document.getElementById('dreg-dr-step3');
                    var stepsAnimWrap = form.querySelector('.dreg-dr-steps-anim-wrap');
                    var items = [
                        document.getElementById('dreg-dr-stepIndicator1') ? document.getElementById('dreg-dr-stepIndicator1').closest('.dreg-dr-step-item') : null,
                        document.getElementById('dreg-dr-stepIndicator2') ? document.getElementById('dreg-dr-stepIndicator2').closest('.dreg-dr-step-item') : null,
                        document.getElementById('dreg-dr-stepIndicator3') ? document.getElementById('dreg-dr-stepIndicator3').closest('.dreg-dr-step-item') : null
                    ];
                    var indicators = [
                        document.getElementById('dreg-dr-stepIndicator1'),
                        document.getElementById('dreg-dr-stepIndicator2'),
                        document.getElementById('dreg-dr-stepIndicator3')
                    ];
                    var labels = [
                        document.getElementById('dreg-dr-stepLabel1'),
                        document.getElementById('dreg-dr-stepLabel2'),
                        document.getElementById('dreg-dr-stepLabel3')
                    ];
                    var stepImages = {
                        1: <?php echo wp_json_encode($assets['step1']); ?>,
                        2: <?php echo wp_json_encode($assets['step2']); ?>,
                        3: <?php echo wp_json_encode($assets['step3']); ?>
                    };

                    function updateStepIndicators(step) {
                        indicators.forEach(function (el, idx) {
                            if (!el) return;
                            var stepNum = idx + 1;
                            el.classList.toggle('is-active', stepNum === step);
                            el.classList.toggle('is-completed', stepNum < step);
                        });
                        labels.forEach(function (el, idx) {
                            if (!el) return;
                            var stepNum = idx + 1;
                            el.classList.toggle('is-active', stepNum === step);
                            el.classList.toggle('is-completed', stepNum < step);
                        });
                        items.forEach(function (el, idx) {
                            if (!el) return;
                            var stepNum = idx + 1;
                            el.classList.toggle('is-active', stepNum === step);
                            el.classList.toggle('is-completed', stepNum < step);
                        });
                    }

                    function updateStepChrome(step) {
                        updateStepIndicators(step);
                        if (stepImage && stepImages[step]) {
                            stepImage.src = stepImages[step];
                        }
                    }

                    function showStep(step, instant) {
                        var steps = [step1, step2, step3];
                        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                        if (reduceMotion) {
                            instant = true;
                        }
                        if (!instant && step === currentStep) {
                            return;
                        }
                        if (!instant && stepAnimLock) {
                            return;
                        }
                        if (!stepsAnimWrap) {
                            currentStep = step;
                            steps.forEach(function (el, idx) {
                                if (!el) return;
                                el.style.display = (idx + 1 === step) ? 'block' : 'none';
                            });
                            updateStepChrome(step);
                            var container = document.querySelector('.dreg-dr-container');
                            window.scrollTo({ top: container.getBoundingClientRect().top + 80, behavior: 'smooth' });
                            return;
                        }
                        if (instant || step === currentStep) {
                            stepAnimLock = false;
                            stepsAnimWrap.removeAttribute('data-dreg-dir');
                            steps.forEach(function (el, idx) {
                                if (!el) return;
                                el.classList.remove('dreg-dr-step-leaving', 'dreg-dr-step-enter');
                                el.classList.toggle('dreg-dr-step-active', idx + 1 === step);
                            });
                            currentStep = step;
                            updateStepChrome(step);
                            var container = document.querySelector('.dreg-dr-container');
                            window.scrollTo({ top: container.getBoundingClientRect().top + 80, behavior: 'smooth' });
                         
                            return;
                        }
                        var outgoing = steps[currentStep - 1];
                        var incoming = steps[step - 1];
                        if (!outgoing || !incoming) {
                            return;
                        }
                        stepAnimLock = true;
                        stepsAnimWrap.setAttribute('data-dreg-dir', step > currentStep ? 'next' : 'prev');
                        updateStepIndicators(step);
                        outgoing.classList.add('dreg-dr-step-leaving');
                        incoming.classList.add('dreg-dr-step-active', 'dreg-dr-step-enter');
                        requestAnimationFrame(function () {
                            requestAnimationFrame(function () {
                                incoming.classList.remove('dreg-dr-step-enter');
                            });
                        });
                        window.setTimeout(function () {
                            outgoing.classList.remove('dreg-dr-step-active', 'dreg-dr-step-leaving');
                            stepAnimLock = false;
                            currentStep = step;
                            stepsAnimWrap.removeAttribute('data-dreg-dir');
                            if (stepImage && stepImages[step]) {
                                stepImage.src = stepImages[step];
                            }
                            var container = document.querySelector('.dreg-dr-container');
                            window.scrollTo({ top: container.getBoundingClientRect().top + 80, behavior: 'smooth' });
                        }, stepAnimMs);
                    }

                    function formatPhone(value) {
                        if (!value) return value;
                        var digits = value.replace(/\D/g, '').slice(0, 10);
                        if (digits.length <= 3) return digits;
                        if (digits.length <= 6) return '(' + digits.slice(0, 3) + ') ' + digits.slice(3);
                        return '(' + digits.slice(0, 3) + ') ' + digits.slice(3, 6) + '-' + digits.slice(6);
                    }

                    var phone = document.getElementById('dreg-dr-phone');
                    if (phone) {
                        phone.addEventListener('input', function () {
                            phone.value = formatPhone(phone.value);
                            syncFieldVisual(phone, false);
                        });
                    }

                    function hideDrErr(id) {
                        var n = document.getElementById(id);
                        if (n) n.style.display = 'none';
                    }

                    function showDrErr(id) {
                        var n = document.getElementById(id);
                        if (n) n.style.display = 'block';
                    }

                    function syncFieldVisual(el, isBlur) {
                        if (!el || !form.contains(el)) return;
                        if (el.type === 'submit' || el.type === 'button') return;

                        if (el.type === 'radio' && el.name) {
                            var esc = el.name.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                            var radios = form.querySelectorAll('input[type="radio"][name="' + esc + '"]');
                            if (!radios.length) return;
                            var tracked = ['category', 'patientsType', 'bottles', 'vitamins', 'formula', 'language', 'sampleFrequency', 'nuOfSamples'];
                            var req = false;
                            radios.forEach(function (r) { if (r.required) req = true; });
                            if (!req && tracked.indexOf(el.name) === -1) return;
                            var checked = form.querySelector('input[type="radio"][name="' + esc + '"]:checked');
                            var ok = !!checked;
                            radios.forEach(function (r) {
                                r.classList.remove('is-valid', 'is-invalid');
                                if (ok && r.checked) r.classList.add('is-valid');
                                if (!ok) r.classList.add('is-invalid');
                            });
                            return;
                        }

                        if (el.type === 'checkbox' && el.required) {
                            el.classList.toggle('is-valid', el.checked);
                            el.classList.toggle('is-invalid', !el.checked);
                            return;
                        }

                        if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT' && el.tagName !== 'TEXTAREA') return;

                        var v = (el.value || '').trim();
                        var id = el.id;

                        if (el.required && v === '') {
                            el.classList.remove('is-valid');
                            if (isBlur) el.classList.add('is-invalid');
                            else el.classList.remove('is-invalid');
                            if (id === 'dreg-dr-email') hideDrErr('dreg-dr-emailError');
                            if (id === 'dreg-dr-phone') hideDrErr('dreg-dr-phoneError');
                            if (id === 'dreg-dr-extension') hideDrErr('dreg-dr-extensionError');
                            if (id === 'dreg-dr-postalCode') hideDrErr('dreg-dr-postalCodeError');
                            if (id === 'dreg-dr-numberOfPatients') hideDrErr('dreg-dr-numberOfPatientsError');
                            return;
                        }

                        var ok = true;

                        if (id === 'dreg-dr-email') {
                            ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
                            if (!ok) showDrErr('dreg-dr-emailError'); else hideDrErr('dreg-dr-emailError');
                        } else if (id === 'dreg-dr-phone') {
                            ok = /^\(\d{3}\) \d{3}-\d{4}$/.test(v);
                            if (!ok) showDrErr('dreg-dr-phoneError'); else hideDrErr('dreg-dr-phoneError');
                        } else if (id === 'dreg-dr-extension') {
                            ok = v === '' || /^[0-9]+$/.test(v);
                            if (!ok) showDrErr('dreg-dr-extensionError'); else hideDrErr('dreg-dr-extensionError');
                        } else if (id === 'dreg-dr-postalCode') {
                            ok = /^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/.test(v);
                            if (!ok) showDrErr('dreg-dr-postalCodeError'); else hideDrErr('dreg-dr-postalCodeError');
                        } else if (id === 'dreg-dr-numberOfPatients') {
                            ok = /^[0-9]+$/.test(v);
                            if (!ok) showDrErr('dreg-dr-numberOfPatientsError'); else hideDrErr('dreg-dr-numberOfPatientsError');
                        } else if (el.name === 'fName[]' || el.name === 'lName[]') {
                            ok = v !== '';
                        }

                        if (!ok) {
                            el.classList.remove('is-valid');
                            el.classList.add('is-invalid');
                            return;
                        }

                        el.classList.remove('is-invalid');
                        el.classList.add('is-valid');
                    }

                    form.addEventListener('input', function (e) {
                        var t = e.target;
                        if (!form.contains(t)) return;
                        syncFieldVisual(t, false);
                    });
                    form.addEventListener('change', function (e) {
                        var t = e.target;
                        if (!form.contains(t)) return;
                        syncFieldVisual(t, true);
                    });
                    form.addEventListener('blur', function (e) {
                        var t = e.target;
                        if (!form.contains(t)) return;
                        syncFieldVisual(t, true);
                    }, true);

                    function validateStep(stepEl) {
                        var isValid = true;
                        var required = stepEl.querySelectorAll('[required]');

                        required.forEach(function (el) {
                            if (el.type === 'radio') {
                                var group = stepEl.querySelectorAll('input[type="radio"][name="' + el.name.replace(/"/g, '\\"') + '"]');
                                var anyChecked = false;
                                group.forEach(function (g) { if (g.checked) anyChecked = true; });
                                group.forEach(function (g) {
                                    g.classList.toggle('is-invalid', !anyChecked);
                                    g.classList.toggle('is-valid', anyChecked && g.checked);
                                });
                                if (!anyChecked) isValid = false;
                                return;
                            }

                            if (el.type === 'checkbox') {
                                el.classList.toggle('is-invalid', !el.checked);
                                el.classList.toggle('is-valid', el.checked);
                                if (!el.checked) isValid = false;
                                return;
                            }

                            if (el.value === '') {
                                el.classList.add('is-invalid');
                                el.classList.remove('is-valid');
                                isValid = false;
                            } else {
                                el.classList.remove('is-invalid');
                                el.classList.add('is-valid');
                            }
                        });

                        if (stepEl === step1) {
                            var email = document.getElementById('dreg-dr-email');
                            var emailError = document.getElementById('dreg-dr-emailError');
                            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (email && !emailPattern.test(email.value || '')) {
                                isValid = false;
                                email.classList.add('is-invalid');
                                email.classList.remove('is-valid');
                                if (emailError) emailError.style.display = 'block';
                            } else if (email) {
                                email.classList.remove('is-invalid');
                                email.classList.add('is-valid');
                                if (emailError) emailError.style.display = 'none';
                            }

                            var phoneEl = document.getElementById('dreg-dr-phone');
                            var phoneError = document.getElementById('dreg-dr-phoneError');
                            var phonePattern = /^\(\d{3}\) \d{3}-\d{4}$/;
                            if (phoneEl && !phonePattern.test(phoneEl.value || '')) {
                                isValid = false;
                                phoneEl.classList.add('is-invalid');
                                phoneEl.classList.remove('is-valid');
                                if (phoneError) phoneError.style.display = 'block';
                            } else if (phoneEl) {
                                phoneEl.classList.remove('is-invalid');
                                phoneEl.classList.add('is-valid');
                                if (phoneError) phoneError.style.display = 'none';
                            }

                            var extEl = document.getElementById('dreg-dr-extension');
                            var extErr = document.getElementById('dreg-dr-extensionError');
                            var extPattern = /^[0-9]+$/;
                            if (extEl && extEl.value !== '' && !extPattern.test(extEl.value)) {
                                isValid = false;
                                extEl.classList.add('is-invalid');
                                extEl.classList.remove('is-valid');
                                if (extErr) extErr.style.display = 'block';
                            } else if (extEl) {
                                extEl.classList.remove('is-invalid');
                                extEl.classList.add('is-valid');
                                if (extErr) extErr.style.display = 'none';
                            }

                            var postal = document.getElementById('dreg-dr-postalCode');
                            var postalErr = document.getElementById('dreg-dr-postalCodeError');
                            var postalPattern = /^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/;
                            if (postal && !postalPattern.test((postal.value || '').trim())) {
                                isValid = false;
                                postal.classList.add('is-invalid');
                                postal.classList.remove('is-valid');
                                if (postalErr) postalErr.style.display = 'block';
                            } else if (postal) {
                                postal.classList.remove('is-invalid');
                                postal.classList.add('is-valid');
                                if (postalErr) postalErr.style.display = 'none';
                            }
                        }

                        if (stepEl === step2) {
                            var num = document.getElementById('dreg-dr-numberOfPatients');
                            var numErr = document.getElementById('dreg-dr-numberOfPatientsError');
                            var numPattern = /^[0-9]+$/;
                            if (num && !numPattern.test((num.value || '').trim())) {
                                isValid = false;
                                num.classList.add('is-invalid');
                                num.classList.remove('is-valid');
                                if (numErr) numErr.style.display = 'block';
                            } else if (num) {
                                num.classList.remove('is-invalid');
                                num.classList.add('is-valid');
                                if (numErr) numErr.style.display = 'none';
                            }
                        }

                        return isValid;
                    }

                    form.querySelectorAll('.dreg-dr-next').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var stepEl = currentStep === 1 ? step1 : (currentStep === 2 ? step2 : step3);
                            if (!stepEl) return;
                            if (!validateStep(stepEl)) return;
                            showStep(Math.min(3, currentStep + 1));
                        });
                    });

                    form.querySelectorAll('.dreg-dr-prev').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            showStep(Math.max(1, currentStep - 1));
                        });
                    });

                    var addDoctor = document.getElementById('dreg-dr-add-doctor');
                    var doctorsWrap = document.getElementById('dreg-dr-doctors');
                    if (addDoctor && doctorsWrap) {
                        addDoctor.addEventListener('click', function (e) {
                            e.preventDefault();
                            var first = doctorsWrap.querySelector('.dreg-dr-doctor-row');
                            if (!first) return;
                            var clone = first.cloneNode(true);
                            clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
                            clone.querySelectorAll('select').forEach(function (s) { s.value = ''; });
                            var removeBtn = clone.querySelector('.dreg-dr-remove-doctor');
                            if (removeBtn) {
                                removeBtn.style.display = 'inline-block';
                                removeBtn.addEventListener('click', function () {
                                    clone.remove();
                                });
                            }
                            doctorsWrap.appendChild(clone);
                        });
                    }

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (!validateStep(step1)) {
                            showStep(1, true);
                            return;
                        }
                        if (!validateStep(step2)) {
                            showStep(2, true);
                            return;
                        }
                        if (!validateStep(step3)) {
                            showStep(3, true);
                            return;
                        }
                        if (typeof form.submit === 'function') {
                            form.submit();
                        }
                    });

                    showStep(1, true);
                });
                </script>
            <?php endif; ?>
        </div>
    </main>
    <?php

    return ob_get_clean();
}
