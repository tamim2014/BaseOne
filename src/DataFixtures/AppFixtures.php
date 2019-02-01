<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        // on fait 3 catégories fakées
        for($i = 1; $i <= 10; $i++){
            $category = new Category();
            //source:https://github.com/fzaninotto/Faker
            $category->setTitle($faker->sentence())
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            //On met 4 à 6 articles dans chaque Catégory
            for($j=1; $j<=mt_rand(4, 6); $j++){

                $content = '<p>'.join($faker->paragraphs(5), '</p><p>').'</p>';
                
                $article = new Article();
                $article->setTitre($faker->sentence())
                        ->setAuteur($faker->name)
                        ->setContenu($content)
                        ->setImage($faker->imageUrl() )
                        ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);

                $manager->persist($article);
                
                //On donne des commentaires à l'article
                for($k = 1; $k <= mt_rand(4, 10); $k++){
                   $comment = new Comment();

                   $content = '<p>'.join($faker->paragraphs(2), '</p><p>').'</p>';

                   $now = new \DateTime();
                   $interval = $now->diff($article->getCreatedAt());
                   $jours = $interval->days;
                   $minimum = '-'.$jours. 'days'; // -100 days

                   $comment->setAuthor($faker->name)
                           ->setContent($content)
                           ->setCreatedAt($faker->dateTimeBetween($minimum))
                           ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}

