@if(isset($errors))
  @if (count($errors) > 0)
  <div id="error-alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
    <ul>
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
      <svg onclick="document.getElementById('error-alert').classList.add('hidden')" class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
    </span>
  </div>
  @endif
  @if(Session::has('flash_message'))
    @if( Session::get('flash_message_level') == "success" )
    <div id="success-alert" class="relative p-4 mb-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-lg" role="alert">
    @else( Session::get('flash_message_level') == "danger" )
    <div id="error-alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
    @endif
      <ul>
        {!! Session::get('flash_message') !!}
      </ul>
      <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
        <svg onclick="document.getElementById('error-alert').classList.add('hidden')" class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
      </span>
    </div>
  @endif
@endif
