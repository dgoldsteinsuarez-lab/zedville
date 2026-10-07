<div class="themeHeader fixed-top">
    <div class="container-fluid d-flex">
        <div class="thnavWrapper navWrapper w-100">
            <nav class="navbar navbar-expand">
                <div class="container-fluid">
                    <button class="navToggleBtn">
                        <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.34961 1.07117L1.42057 6.00021L6.34961 10.9293" stroke="#222222" stroke-width="1.4" stroke-linecap="square"/>
                        </svg>
                    </button>
                    <div class="ps-2">
                        @include('components.school-badge')
                    </div>



                    <div class="collapse navbar-collapse themeHeaderNav justify-content-end">
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <span class="navIcon">
                                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12.1357 16C17.6586 16 22.1357 14.2092 22.1357 12C22.1357 9.79085 17.6586 8 12.1357 8C6.61289 8 2.13574 9.79085 2.13574 12C2.13574 14.2092 6.61289 16 12.1357 16Z" stroke="currentColor" stroke-width="1.4"/>
                                            <path d="M16.1357 12C16.1357 17.5229 14.3449 22 12.1357 22C9.92659 22 8.13574 17.5229 8.13574 12C8.13574 6.47715 9.92659 2 12.1357 2C14.3449 2 16.1357 6.47715 16.1357 12Z" stroke="currentColor" stroke-width="1.4"/>
                                            <path d="M12.1357 22C17.6586 22 22.1357 17.5228 22.1357 12C22.1357 6.47715 17.6586 2 12.1357 2C6.61289 2 2.13574 6.47715 2.13574 12C2.13574 17.5228 6.61289 22 12.1357 22Z" stroke="currentColor" stroke-width="1.4"/>
                                        </svg>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end themeDropdown langCureDropdown">
                                    <li>
                                        <div class="dropdown-item">
                                            <div class="navdropExit curlanItems">
                                                <div class="leftConten">
                                                    <span class="icon">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12 1V23" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M17.5 3C17.5 3 12.4853 3 10 3C7.5147 3 5.5 5.0147 5.5 7.5C5.5 9.9853 7.5147 12 10 12" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M6.5 21C6.5 21 11.5147 21 14 21C16.4853 21 18.5 18.9853 18.5 16.5C18.5 14.0147 16.4853 12 14 12H10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                    <span class="navTxt">Currency(ZED)</span>
                                                </div>
                                                <div class="rightConten">
                                                    <!-- <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#currencyModal">Edit</a> -->
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-item">
                                            <div class="navdropExit curlanItems">
                                                <div class="dropdownaccord accordion" id="languageEnglish">
                                                    <div class="accordion-item">
                                                        <div class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#laguangAccord" aria-expanded="false" aria-controls="collapseTwo">
                                                                <span class="icon">
                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M14.1428 18.5H19.8571M14.1428 18.5L13 21M14.1428 18.5L17 12L19.8571 18.5M19.8571 18.5L21 21" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M8 3L8.5 4.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M3 5.5H14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M5 8C5 8 5.89475 11.1304 8.1316 12.8695C10.3684 14.6087 14 16 14 16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M12 5.5C12 5.5 11.1052 9.6087 8.8684 11.8913C6.6316 14.1739 3 16 3 16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </span>
                                                                <span class="txt">Language(US)</span>
                                                            </button>
                                                        </div>
                                                        <div id="laguangAccord" class="accordion-collapse collapse" data-bs-parent="#languageEnglish">
                                                            <div class="accordion-body">
                                                                <ul class="languageItemsList">
                                                                    <li><a href="javascript:void(0);">English (US)</a></li>
                                                                    <li><a href="javascript:void(0);">Spanish (ES)</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="navIcon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 19V9C5 5.134 8.134 2 12 2C15.866 2 19 5.134 19 9V19M2 19H22" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 22C13.3807 22 14.5 20.8807 14.5 19.5V19H9.5V19.5C9.5 20.8807 10.6193 22 12 22Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <span class="navImg">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.56701 13.933 3 12 3C10.067 3 8.5 4.56701 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="navTxt">{{ Auth::user()->name }}</span>
                                    <span class="navarrow"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end themeDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ url('admin/profile') }}">
                                            <div class="accountDrop">
                                                <div class="userimgsec">
                                                    <span class="textname">{{ Auth::user()->name }}</span>
                                                </div>
                                                <div class="userpointDtls">
                                                    <div class="unameLabel">
                                                        <span>{{ Auth::user()->name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <form  action="{{ url('logout') }}" method="POST" >
                                            @csrf
                                        <a class="dropdown-item" href="{{ url('logout') }}" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                            <div class="navdropExit">
                                                <span class="icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.9958 3H3V21H12" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M16.5 16.5L21 12L16.5 7.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8 11.9958H21" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                                <span class="navTxt">Exit</span>
                                            </div>
                                        </a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>

<!-- Currency Modal -->

<div class="themeModal">
    <div class="modal fade" id="currencyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="currencyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-16 fw-500 theme-text-dark">Currency setting</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="currencyForm themeForm">
                        <form action="#" method="post">
                            <h4 class="currencyFormtitle fs-16 fw-500 mab-15 theme-text-dark">Site browsing currency</h4>
                            <div class="modalSeclect">
                                <select name="" class="form-control modalCurrencySelect">
                                    <option value="USD | US Dollar">USD | US Dollar</option>
                                    <option value="EUR | Euro">EUR | Euro</option>
                                    <option value="GBP | Pound Sterling">GBP | Pound Sterling</option>
                                    <option value="JPY | Japanese yen">JPY | Japanese yen</option>
                                    <option value="AUD | Australian Dollar">AUD | Australian Dollar</option>
                                    <option value="BRL | Brazilian Real">BRL | Brazilian Real</option>
                                    <option value="CAD | Canadian Dollar">CAD | Canadian Dollar</option>
                                    <option value="CHF | Swiss Franc">CHF | Swiss Franc</option>
                                    <option value="DKK | Danish Krone">DKK | Danish Krone</option>
                                    <option value="HKD | Hong Kong Dollar">HKD | Hong Kong Dollar</option>
                                    <option value="MXN | Mexican Peso">MXN | Mexican Peso</option>
                                    <option value="NZD | New Zealand Dollar">NZD | New Zealand Dollar</option>
                                    <option value="SEK | Swedish Krona">SEK | Swedish Krona</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="secondaryBtn theme-text-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="themeBtn text-white">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Currency Modal End -->

<!-- Change School Modal -->
<div class="themeModal">
    <div class="modal fade" id="editmodel`" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editmodelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-16 fw-500 theme-text-dark">Change School</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="themeForm">
                        <form method="POST" action="{{ url('admin/school') }}" id="changeSchoolForm">
                             @csrf
                            <div class="mb-3">
                                <label for="schoolSelect" class="form-label fs-14 fw-500 theme-text-dark">Select School</label>
                                <select name="search" id="schoolSelect" class="form-control form-select fs-14" required>
                                    <option value="">Select School User</option>
                                    @php
                                        $schools = \App\Models\SchoolDomain::all();
                                    @endphp
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" 
                                            {{ session('selected_school') == $school->id ? 'selected' : '' }}>
                                            {{ $school->school_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="secondaryBtn theme-text-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="themeBtn text-white" onclick="document.getElementById('changeSchoolForm').submit();">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Change School Modal End -->