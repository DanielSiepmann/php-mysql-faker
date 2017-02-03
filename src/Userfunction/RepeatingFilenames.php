<?php

namespace DSiepmann\Userfunction;

use Faker\Factory as Faker;

class RepeatingFilenames
{
    protected $fileExtensions = ['jpg', 'png', 'pdf'];
    protected $fileNames = [];

    /**
     * @var Faker\Generator
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
        foreach (range(0, 12) as $number) {
            $this->fileNames[] = $this->faker->word . '.' . $this->faker->randomElement($this->fileExtensions);
        }
    }

    public function filename()
    {
        return $this->faker->randomElement($this->fileNames);
    }
}
