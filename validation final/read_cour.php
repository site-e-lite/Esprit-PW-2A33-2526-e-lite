<?php
$base = 'c:\\Users\\boujm\\Desktop\\boj web\\cour';
$files = [
    'config.php',
    'index.php',
    'Controller/CourseController.php',
    'Controller/EnrollmentController.php',
    'Controller/CertificateController.php',
    'Controller/ProgressController.php',
    'Controller/RatingController.php',
    'Controller/SupportCourseController.php',
    'Model/Course.php',
    'Model/CourseModel.php',
    'Model/Enrollment.php',
    'Model/EnrollmentModel.php',
    'Model/Certificate.php',
    'Model/Lesson.php',
    'Model/Progress.php',
    'Model/Rating.php',
    'Model/SupportCourse.php',
    'Utils/Validator.php',
    'View/FrontOffice/index.php',
    'View/FrontOffice/courses_list.php',
    'View/FrontOffice/course/index.php',
    'View/FrontOffice/course/show.php',
    'View/FrontOffice/course_detail.php',
    'View/FrontOffice/my_courses.php',
    'View/FrontOffice/enrollment/add.php',
    'View/FrontOffice/enrollment_form.php',
    'View/FrontOffice/certificate/index.php',
    'View/FrontOffice/certificate/view.php',
    'View/FrontOffice/course_recommendation.php',
    'View/BackOffice/dashboard.php',
    'View/BackOffice/course/list.php',
    'View/BackOffice/course/add.php',
    'View/BackOffice/course/edit.php',
    'View/BackOffice/enrollment/list.php',
    'View/BackOffice/enrollment/add.php',
    'View/BackOffice/enrollment/edit.php',
    'View/BackOffice/certificate/list.php',
    'View/BackOffice/support_course/list.php',
    'View/BackOffice/support_course/add.php',
    'View/BackOffice/support_course/edit.php',
    'View/includes/header.php',
    'View/includes/footer.php',
    'setup_lessons.php',
    'setup_certificates.php',
];

foreach ($files as $f) {
    $path = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $f);
    echo "\n\n========== FILE: $f ==========\n";
    if (file_exists($path)) {
        echo file_get_contents($path);
    } else {
        echo "[FILE NOT FOUND]";
    }
}
