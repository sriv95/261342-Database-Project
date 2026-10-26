@php($p = $product ?? null)
<div class="grid gap-3 text-sm sm:grid-cols-2">
    <div>
        <label class="block font-medium" for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $p?->name) }}" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
        @error('name') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="flex items-end gap-2">
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $p?->is_active ?? true))> Visible in shop</label>
    </div>
    <div>
        <label class="block font-medium" for="category_id">Category</label>
        <select id="category_id" name="category_id" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $p?->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="block font-medium" for="brand_id">Brand</label>
        <select id="brand_id" name="brand_id" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            @foreach ($brands as $b)<option value="{{ $b->id }}" @selected(old('brand_id', $p?->brand_id) == $b->id)>{{ $b->name }}</option>@endforeach
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="block font-medium" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">{{ old('description', $p?->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="block font-medium" for="image">Image (optional, max 2 MB)</label>
        <input id="image" type="file" name="image" accept="image/*" class="mt-1 text-sm">
        @error('image') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
