@extends('layouts.master')

@section('title', $cert->name.'证照信息')
@section('customer', 'active')
@section('css_link')
    <link href="{{ get_static('assets/plugins/gridgallery/css/component.css')}}" rel="stylesheet" type="text/css"/>
@endsection
@section('nav', '证照信息')

@section('content')
    <div class="col-md-2">
        @include('layouts.customer_menu')
    </div>
    <div class="col-md-10">
        <div class="row">
            <div class="panel panel-white">
                <div class="panel-heading clearfix">
                    <h4 class="panel-title">{{$cert['name']}}</h4>
                </div>

                <div class="panel-body">
                        <div id="grid-gallery" class="grid-gallery">
                            <section class="grid-wrap">
                                <ul class="grid">
                                    <li class="grid-sizer"></li>
                                    @foreach(unserialize($cert['certificate_path']) as $key => $value)
                                    <li>
                                        <figure>
                                            <img src="{{$value}}" alt="{{$key}}"/>
                                        </figure>
                                    </li>
                                    @endforeach
                                </ul>
                            </section>
                            <section class="slideshow">
                                <ul>
                                    @foreach(unserialize($cert['certificate_path']) as $key => $value)

                                    <li>
                                        <figure>
                                            <figcaption>
                                                <h3>{{$cert['name']}}</h3>
                                                <p><a href="{{$value}}" target="_blank"><i class="fa fa-picture-o"></i>&nbsp;查看原图</a> </p>
                                                <p><a href="{{action('CertificateController@getDownloadCertificate', $customer_id)}}?url={{$value}}" target="_blank"><i class="fa fa-download"></i>&nbsp;下载此文件</a>({{get_file_size($value)}} Kb)</p>
                                                <p>{{$cert['remarks']}}</p>
                                            </figcaption>
                                            <img src="{{$value}}" alt="{{$key}}"/>
                                        </figure>
                                    </li>
                                    @endforeach

                                </ul>
                                <nav>
                                    <span class="fa fa-angle-left nav-prev"></span>
                                    <span class="fa fa-angle-right nav-next"></span>
                                    <span class="fa fa-times nav-close"></span>
                                </nav>
                            </section>
                        </div>
                 </div>
            </div>
    </div>
        </div>
@endsection
@section('javascript')
    <script src="{{ get_static('assets/plugins/toastr/toastr.min.js')}}"></script>
    <script src="{{ get_static('assets/js/pages/notifications.js')}}"></script>
            <script src="{{ get_static('assets/plugins/gridgallery/js/imagesloaded.pkgd.min.js')}}"></script>
            <script src="{{ get_static('assets/plugins/gridgallery/js/masonry.pkgd.min.js')}}"></script>
    <script src="{{ get_static('assets/plugins/gridgallery/js/classie.js')}}"></script>
    <script src="{{ get_static('assets/plugins/gridgallery/js/cbpgridgallery.js')}}"></script>
    <script src="{{ get_static('assets/js/pages/gallery.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function () {

            @if(Session::has('success'))
            setTimeout(function () {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut',
                    timeOut: 5000
                };
                toastr.success('{{Session::get('success')}}', '操作成功');
            }, 1800);
            @endif
             @if(Session::has('error'))
            setTimeout(function () {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut',
                    timeOut: 5000
                };
                toastr.error('{{Session::get('error')}}', '发生错误');
            }, 1800);
            @endif


            })
    </script>
@endsection
