<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Article extends Eloquent
{
  use HasFactory;

  protected $connection = 'mongodb';
  protected $collection = 'article';

  protected $fillable = [
    'id_category',
    'title',
    'slug',
    'small_text',
    'long_text',
    'url_image',
    'creation_date',
    'update_date'
  ];
}
