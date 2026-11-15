<button type="button" class="{{ $buttonClass }}" data-log-copy="log-copy-{{ $key }}" data-success="@lang('Copied')" data-error="@lang('Copy failed. Select and copy the text below.')">@lang('Copy details')</button>
<label for="log-copy-{{ $key }}" data-copy-label hidden>@lang('Log details')</label>
<textarea id="log-copy-{{ $key }}" hidden readonly rows="6" style="width: 100%; min-width: 200px; text-align: left;" aria-label="@lang('Log details')">{{ $entry->dettagliDaCopiare() }}</textarea>
<span data-copy-status role="status" aria-live="polite"></span>
