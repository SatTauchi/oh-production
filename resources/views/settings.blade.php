@extends('layouts.app')

@section('title', '管理者設定 - おさかなハぅマっチ？')

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-white rounded-3xl shadow-lg p-4 sm:p-6 md:p-8 transition duration-300 ease-in-out hover:shadow-xl mb-8">
        <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-blue-500 after:rounded-full">管理者設定</h2>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm">ID</th>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm">名前</th>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm hidden sm:table-cell">メールアドレス</th>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm">アカウント</th>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm">権限</th>
                        <th class="py-2 px-2 sm:px-4 border-b text-left text-xs sm:text-sm">アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm">{{ $user->id }}</td>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm">{{ $user->name }}</td>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm hidden sm:table-cell">{{ $user->email }}</td>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm">
                            @if($user->life_flg == 0)
                                <span class="text-green-500">有効</span>
                            @else
                                <span class="text-red-500">退会</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm">
                            @if($user->admin_flg == 1)
                                <span class="text-blue-500">管理者</span>
                            @else
                                <span>一般</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 sm:px-4 border-b text-xs sm:text-sm">
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                <form action="{{ route('settings.toggle-life', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs sm:text-sm">
                                        {{ $user->life_flg == 0 ? '退会処理' : '復帰処理' }}
                                    </button>
                                </form>
                                <form action="{{ route('settings.toggle-admin', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full sm:w-auto bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs sm:text-sm">
                                        {{ $user->admin_flg == 1 ? '権限解除' : '権限付与' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection