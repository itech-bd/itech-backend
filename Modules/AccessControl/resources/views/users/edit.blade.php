<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Edit User') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 sm:p-8">
					<div class="mb-6">
						<h3 class="text-lg font-semibold text-gray-900">Edit User</h3>
						<p class="mt-1 text-sm text-gray-600">Update user roles and submit.</p>
						<div class="mt-3">
							<a href="/admin/users/{{ $user->getRouteKey() }}/profile" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
								Edit Profile Details
							</a>
						</div>
					</div>

					<form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
						@csrf
						@method('PUT')

						<div>
							<label for="name" class="block text-sm font-medium text-gray-700">Name</label>
							<div class="mt-2">
								<input
									id="name"
									disabled
									type="text"
									autocomplete="off"
									value="{{ $user->name }}"
									class="block w-full rounded-md border-gray-200 bg-gray-50 text-gray-700 shadow-sm sm:text-sm"
								/>
							</div>
						</div>

						<div>
							<label for="email" class="block text-sm font-medium text-gray-700">Email</label>
							<div class="mt-2">
								<input
									id="email"
									disabled
									type="email"
									autocomplete="off"
									value="{{ $user->email }}"
									class="block w-full rounded-md border-gray-200 bg-gray-50 text-gray-700 shadow-sm sm:text-sm"
								/>
							</div>
						</div>

						<div>
							@if($roles->isNotEmpty())
								<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
									<div>
										<h4 class="text-sm font-medium text-gray-700">Assign Roles</h4>
										<p class="mt-1 text-xs text-gray-500">Select one or more roles for this user.</p>
									</div>

									<div class="flex items-center gap-3">
										<label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
											<input
												id="roles-select-all"
												type="checkbox"
												class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
											/>
											<span>Select all</span>
										</label>

										<div class="relative">
											<input
												id="roles-search"
												type="text"
												autocomplete="off"
												placeholder="Search roles..."
												class="block w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
											/>
										</div>
									</div>
								</div>

								<div class="mt-4 rounded-lg border border-gray-200 bg-gray-50/40 p-3">
									<div id="roles-grid" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
										@foreach($roles as $role)
											<label
												class="role-item group flex cursor-pointer items-start gap-3 rounded-md border border-gray-200 bg-white p-3 shadow-sm transition hover:border-indigo-200 hover:shadow"
												data-role-name="{{ strtolower($role->name) }}"
											>
												<input
													type="checkbox"
													name="roles[]"
													value="{{ $role->id }}"
													@checked(in_array($role->id, old('roles', $userRoleIds)))
													class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
												/>
												<span class="min-w-0">
													<span class="block truncate text-sm font-medium text-gray-800 group-hover:text-indigo-700">{{ $role->name }}</span>
													<span class="mt-0.5 block text-xs text-gray-500">Role</span>
												</span>
											</label>
										@endforeach
									</div>

									@error('roles')
										<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>
							@endif
						</div>

						<div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
							<div>
								@if(auth()->id() !== $user->id)
									<button
										type="submit"
										form="delete-user-form"
										class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
									>
										Delete
									</button>
								@endif
							</div>

							<div class="flex items-center justify-end gap-3">
							<a
								href="/users"
								class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
							>
								Cancel
							</a>

							<button
								type="submit"
								class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
								style="background-color: #4f46e5; color: #ffffff;"
							>
								Update
							</button>
							</div>
						</div>
					</form>

					@if(auth()->id() !== $user->id)
						<form
							id="delete-user-form"
							action="{{ route('users.destroy', $user) }}"
							method="POST"
							onsubmit="return confirm('Delete this user? This action cannot be undone.');"
							class="hidden"
						>
							@csrf
							@method('DELETE')
						</form>
					@endif

					@if($roles->isNotEmpty())
						@push('scripts')
							<script>
								(function () {
									const grid = document.getElementById('roles-grid');
									const searchInput = document.getElementById('roles-search');
									const selectAll = document.getElementById('roles-select-all');

									if (!grid || !searchInput || !selectAll) return;

									const items = Array.from(grid.querySelectorAll('.role-item'));

									function updateSelectAllState() {
										const visibleCheckboxes = items
											.filter(el => el.style.display !== 'none')
											.map(el => el.querySelector('input[type="checkbox"]'))
											.filter(Boolean);

										if (visibleCheckboxes.length === 0) {
											selectAll.checked = false;
											selectAll.indeterminate = false;
											return;
										}

										const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
										selectAll.checked = checkedCount === visibleCheckboxes.length;
										selectAll.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
									}

									function applyFilter() {
										const q = (searchInput.value || '').trim().toLowerCase();
										items.forEach(el => {
											const name = el.getAttribute('data-role-name') || '';
											el.style.display = name.includes(q) ? '' : 'none';
										});
										updateSelectAllState();
									}

									searchInput.addEventListener('input', applyFilter);

									selectAll.addEventListener('change', function () {
										const shouldCheck = selectAll.checked;
										items
											.filter(el => el.style.display !== 'none')
											.forEach(el => {
												const cb = el.querySelector('input[type="checkbox"]');
												if (cb) cb.checked = shouldCheck;
											});
										updateSelectAllState();
									});

									grid.addEventListener('change', function (e) {
										const target = e.target;
										if (target && target.matches('input[type="checkbox"][name="roles[]"]')) {
											updateSelectAllState();
										}
									});

									updateSelectAllState();
								})();
							</script>
						@endpush
					@endif
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
