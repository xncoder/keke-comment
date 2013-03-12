@echo off
set script_type=autogen
set cwd=%~pd0
set toolkit_path=E:\exdev\tools\exvim\toolkit
set lang_type=c cpp c# javascript java shader python lua vim uc matlab wiki ini make sh batch debug qt swig html
set vimfiles_path=.vimfiles.bole
set file_filter=*.C *.C++ *.CC *.CP *.CPP *.CXX *.H *.H++ *.HH *.HP *.HPP *.HXX *.INL *.IPP *.CS *.JS *.AS *.JAVA *.HLSL *.VSH *.PSH *.FX *.FXH *.CG *.SHD *.GLSL *.PY *.PYW *.PYX *.PXD *.LUA *.WLUA *.VIM *.UC *.M *.WIKI *.INI *.CFG *.MAK *.MK *.MAKEFILE *.SH *.BSH *.BASH *.KSH *.ZSH *.BAT *.LOG *.ERR *.EXE *.QRC *.PRO *.PRI *.I *.SWG *.PHP *.HTM 
set file_filter_pattern="\\.c$|\\.C$|\\.c++$|\\.cc$|\\.cp$|\\.cpp$|\\.cxx$|\\.h$|\\.H$|\\.h++$|\\.hh$|\\.hp$|\\.hpp$|\\.hxx$|\\.inl$|\\.ipp$|\\.cs$|\\.js$|\\.as$|\\.java$|\\.hlsl$|\\.vsh$|\\.psh$|\\.fx$|\\.fxh$|\\.cg$|\\.shd$|\\.glsl$|\\.py$|\\.pyw$|\\.pyx$|\\.pxd$|\\.lua$|\\.wlua$|\\.vim$|\\.uc$|\\.m$|\\.wiki$|\\.ini$|\\.cfg$|\\.mak$|\\.mk$|\\.Makefile$|\\.makefile$|\\.sh$|\\.SH$|\\.bsh$|\\.bash$|\\.ksh$|\\.zsh$|\\.bat$|\\.log$|\\.err$|\\.exe$|\\.qrc$|\\.pro$|\\.pri$|\\.i$|\\.swg$|\\.php$|\\.htm$|\\.js$"
set cscope_file_filter=*.C *.C++ *.CC *.CP *.CPP *.CXX *.H *.H++ *.HH *.HP *.HPP *.HXX *.INL *.IPP *.HLSL *.VSH *.PSH *.FX *.FXH *.CG *.SHD *.GLSL 
set cscope_file_filter_pattern="\\.c$|\\.C$|\\.c++$|\\.cc$|\\.cp$|\\.cpp$|\\.cxx$|\\.h$|\\.H$|\\.h++$|\\.hh$|\\.hp$|\\.hpp$|\\.hxx$|\\.inl$|\\.ipp$|\\.hlsl$|\\.vsh$|\\.psh$|\\.fx$|\\.fxh$|\\.cg$|\\.shd$|\\.glsl$"
set dir_filter=
set support_filenamelist=true
set support_ctags=true
set support_symbol=true
set support_inherit=true
set support_cscope=true
set support_idutils=true
set ctags_cmd=ctags
set ctags_options= --c-kinds=+p --c++-kinds=+p --fields=+iaS --extra=+q --languages=c,c++,c#,javascript,java,python,lua,vim,matlab,make,sh,html,c, --langmap=c:+.C,c++:+.H,c++:+.inl,c++:+.ipp,javascript:+.as,python:+.pyw,lua:+.wlua,c:+.hlsl,c:+.vsh,c:+.psh,c:+.fx,c:+.fxh,c:+.cg,c:+.shd,c:+.glsl,
if exist .\%vimfiles_path%\quick_gen_project_pre_custom.bat (
    call .\%vimfiles_path%\quick_gen_project_pre_custom.bat
)
call "%toolkit_path%\quickgen\batch\quick_gen_project.bat" %1
if exist .\%vimfiles_path%\quick_gen_project_post_custom.bat (
    call .\%vimfiles_path%\quick_gen_project_post_custom.bat
)
echo on
