<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .tree-container {
            border: 2px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .tree {
            list-style-type: none;
            padding-left: 0;
        }

        .tree-node {
            padding: 10px 15px;
            background-color: #4A90E2;
            color: white;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
            position: relative;
        }

        .tree-node::after {
            content: "";
            display: block;
            width: 2px;
            height: 15px;
            background-color: #4A90E2;
            position: absolute;
            left: 50%;
            top: 100%;
        }

        .tree-branch {
            font-weight: bold;
            color: #4A90E2;
            margin-top: 8px;
            display: block;
        }

        ul.border-l {
            border-left: 2px solid #ccc;
        }
    </style>
    @livewireStyles

</head>

<body>



    <livewire:layout.navigation />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <br>
        {{ $slot }}
    </div>



    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</body>


</html>
