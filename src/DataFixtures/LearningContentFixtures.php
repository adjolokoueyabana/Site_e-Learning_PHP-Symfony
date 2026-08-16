<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LearningContentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $music = $this->createTheme(
            $manager,
            'Musique',
            'musique',
            'Formations dédiées à l’apprentissage de la musique.'
        );

        $computerScience = $this->createTheme(
            $manager,
            'Informatique',
            'informatique',
            'Formations dédiées aux bases de l’informatique et du développement.'
        );

        $gardening = $this->createTheme(
            $manager,
            'Jardinage',
            'jardinage',
            'Formations dédiées à l’apprentissage du jardinage.'
        );

        $cooking = $this->createTheme(
            $manager,
            'Cuisine',
            'cuisine',
            'Formations dédiées aux techniques culinaires.'
        );

        $guitarCourse = $this->createCourse(
            $manager,
            $music,
            'Cursus d’initiation à la guitare',
            'initiation-guitare',
            '50.00'
        );

        $this->createLesson(
            $manager,
            $guitarCourse,
            'Découverte de l’instrument',
            'guitare-decouverte-instrument',
            '26.00',
            1
        );

        $this->createLesson(
            $manager,
            $guitarCourse,
            'Les accords et les gammes',
            'guitare-accords-gammes',
            '26.00',
            2
        );

        $pianoCourse = $this->createCourse(
            $manager,
            $music,
            'Cursus d’initiation au piano',
            'initiation-piano',
            '50.00'
        );

        $this->createLesson(
            $manager,
            $pianoCourse,
            'Découverte de l’instrument',
            'piano-decouverte-instrument',
            '26.00',
            1
        );

        $this->createLesson(
            $manager,
            $pianoCourse,
            'Les accords et les gammes',
            'piano-accords-gammes',
            '26.00',
            2
        );

        $webCourse = $this->createCourse(
            $manager,
            $computerScience,
            'Cursus d’initiation au développement web',
            'initiation-developpement-web',
            '60.00'
        );

        $this->createLesson(
            $manager,
            $webCourse,
            'Les langages Html et CSS',
            'langages-html-css',
            '32.00',
            1
        );

        $this->createLesson(
            $manager,
            $webCourse,
            'Dynamiser votre site avec Javascript',
            'dynamiser-site-javascript',
            '32.00',
            2
        );

        $gardeningCourse = $this->createCourse(
            $manager,
            $gardening,
            'Cursus d’initiation au jardinage',
            'initiation-jardinage',
            '30.00'
        );

        $this->createLesson(
            $manager,
            $gardeningCourse,
            'Les outils du jardinier',
            'outils-du-jardinier',
            '16.00',
            1
        );

        $this->createLesson(
            $manager,
            $gardeningCourse,
            'Jardiner avec la lune',
            'jardiner-avec-la-lune',
            '16.00',
            2
        );

        $cookingCourse = $this->createCourse(
            $manager,
            $cooking,
            'Cursus d’initiation à la cuisine',
            'initiation-cuisine',
            '44.00'
        );

        $this->createLesson(
            $manager,
            $cookingCourse,
            'Les modes de cuisson',
            'modes-de-cuisson',
            '23.00',
            1
        );

        $this->createLesson(
            $manager,
            $cookingCourse,
            'Les saveurs',
            'les-saveurs',
            '23.00',
            2
        );

        $platingCourse = $this->createCourse(
            $manager,
            $cooking,
            'Cursus d’initiation à l’art du dressage culinaire',
            'initiation-dressage-culinaire',
            '48.00'
        );

        $this->createLesson(
            $manager,
            $platingCourse,
            'Mettre en œuvre le style dans l’assiette',
            'style-dans-assiette',
            '26.00',
            1
        );

        $this->createLesson(
            $manager,
            $platingCourse,
            'Harmoniser un repas à quatre plats',
            'harmoniser-repas-quatre-plats',
            '26.00',
            2
        );

        $manager->flush();
    }

    private function createTheme(
        ObjectManager $manager,
        string $name,
        string $slug,
        string $description
    ): Theme {
        $theme = new Theme();
        $theme->setName($name);
        $theme->setSlug($slug);
        $theme->setDescription($description);

        $manager->persist($theme);

        return $theme;
    }

    private function createCourse(
        ObjectManager $manager,
        Theme $theme,
        string $title,
        string $slug,
        string $price
    ): Course {
        $course = new Course();
        $course->setTitle($title);
        $course->setSlug($slug);
        $course->setPrice($price);
        $course->setDescription(
            'Découvrez ce cursus Knowledge Learning et progressez à votre rythme.'
        );
        $course->setTheme($theme);

        $manager->persist($course);

        return $course;
    }

    private function createLesson(
        ObjectManager $manager,
        Course $course,
        string $title,
        string $slug,
        string $price,
        int $position
    ): Lesson {
        $lesson = new Lesson();
        $lesson->setTitle($title);
        $lesson->setSlug($slug);
        $lesson->setPrice($price);
        $lesson->setPosition($position);
        $lesson->setCourse($course);
        $lesson->setContent(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '
            . 'Cette fiche de cours sera enrichie avec le contenu pédagogique.'
        );
        $lesson->setVideoUrl(null);

        $manager->persist($lesson);

        return $lesson;
    }
}