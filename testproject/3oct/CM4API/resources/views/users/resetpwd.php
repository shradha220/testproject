
<?php //echo "<pre>"; print_r($des_name); die; ?>
<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div data-original-title="" class="box-header">
            <h2><i class="halflings-icon edit"></i><span class="break"></span>Add Category</h2>
            <div class="box-icon">
                <a class="btn-setting" href="#"><i class="halflings-icon wrench"></i></a>
                <a class="btn-minimize" href="#"><i class="halflings-icon chevron-up"></i></a>
                <a class="btn-close" href="#"><i class="halflings-icon remove"></i></a>
            </div>
        </div>
        <div class="box-content" style="display: block;">
            {!! Form::open(['url' => 'save-password','files' => true,'class'=>'form-horizontal']) !!}
            <fieldset>
                <div class="control-group">
                    <label for="focusedInput" class="control-label">Reset Password</label>
                    <div class="controls">
                        {!! Form::text('name', null,[ 'maxlength'=>'100', 'id'=>'name', 'class' => 'input-xlarge focused','placeholder' => 'Category Name']) !!}

                    </div>
                </div><br/>

              

                <div class="control-group">
                    <label for="fileInput" class="control-label">Status</label>
                    <div class="controls">
                        <span>    {!! Form::select('status', ['' => '--Please Select--', '1' => 'Yes', '2' => 'No']) !!}</span>
                    </div>
                </div><br/>
                <div class="control-group">
                    <label class="control-label">Country</label>
                    <div class="controls">

                        @foreach($active_country as $country)
                        <label class="checkbox inline">
                            <div class="checker" id="uniform-inlineCheckbox1"><span><input type="checkbox" value="{{ $country['country_code'] }}" id="inlineCheckbox1" name='country_list[]'></span></div> {{ $country['Country']}}
                        </label>
                        @endforeach

                    </div>
                </div><br/>
                <link href="{{URL::asset('assets/css/bootstrap-responsive.min.css')}}" rel="stylesheet">
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </fieldset>
            {!! Form::close() !!}

        </div>
    </div>
</div>
