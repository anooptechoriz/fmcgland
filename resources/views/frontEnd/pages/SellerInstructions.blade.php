@extends('layouts.template')
@section('title')
FMCG | Product Details
@endsection
@section('content')
<div class="page-title-area">
  <div class="container">
    <div class="page-title-content">
      <ul>
        <li> <a href="{{ route('home') }}"> Home </a> </li>
        <li class="active">{{ $data->title ?? ''}}</li>
      </ul>
    </div>
  </div>
</div>
<div class="about-area ptb-54 trms-and-condition innerp">
  <div class="container">
    <div class="about-content">
      <!--<h3>{{ $data->title  ?? ''}}</h3>-->
      
      <?php echo $data->page_content; ?>
     
     
     
     
     
     
     
     
     
     
    <div class="gap-30"></div>
  </div>
</div></div>
@endsection
