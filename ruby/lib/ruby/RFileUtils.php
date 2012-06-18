<?php 
# http://www.ruby-doc.org/stdlib-1.9.3/libdoc/fileutils/rdoc/index.html
#
# = fileutils.rb
#
# Copyright (c) 2000-2007 Minero Aoki
#
# This program is free software.
# You can distribute/modify this program under the same terms of ruby.
#
# == module FileUtils
#
# Namespace for several file utility methods for copying, moving, removing, etc.
#
# === Module Functions
#
#   cd(dir, options)
#   cd(dir, options) {|dir| .... }
#   pwd()
#   mkdir(dir, options)
#   mkdir(list, options)
#   mkdir_p(dir, options)
#   mkdir_p(list, options)
#   rmdir(dir, options)
#   rmdir(list, options)
#   ln(old, new, options)
#   ln(list, destdir, options)
#   ln_s(old, new, options)
#   ln_s(list, destdir, options)
#   ln_sf(src, dest, options)
#   cp(src, dest, options)
#   cp(list, dir, options)
#   cp_r(src, dest, options)
#   cp_r(list, dir, options)
#   mv(src, dest, options)
#   mv(list, dir, options)
#   rm(list, options)
#   rm_r(list, options)
#   rm_rf(list, options)
#   install(src, dest, mode = <src's>, options)
#   chmod(mode, list, options)
#   chmod_R(mode, list, options)
#   chown(user, group, list, options)
#   chown_R(user, group, list, options)
#   touch(list, options)
#
# The <tt>options</tt> parameter is a hash of options, taken from the list
# <tt>:force</tt>, <tt>:noop</tt>, <tt>:preserve</tt>, and <tt>:verbose</tt>.
# <tt>:noop</tt> means that no changes are made.  The other two are obvious.
# Each method documents the options that it honours.
#
# All methods that have the concept of a "source" file or directory can take
# either one file or a list of files in that argument.  See the method
# documentation for examples.
#
# There are some `low level' methods, which do not accept any option:
#
#   copy_entry(src, dest, preserve = false, dereference = false)
#   copy_file(src, dest, preserve = false, dereference = true)
#   copy_stream(srcstream, deststream)
#   remove_entry(path, force = false)
#   remove_entry_secure(path, force = false)
#   remove_file(path, force = false)
#   compare_file(path_a, path_b)
#   compare_stream(stream_a, stream_b)
#   uptodate?(file, cmp_list)
#
# == module FileUtils::Verbose
#
# This module has all methods of FileUtils module, but it outputs messages
# before acting.  This equates to passing the <tt>:verbose</tt> flag to methods
# in FileUtils.
#
# == module FileUtils::NoWrite
#
# This module has all methods of FileUtils module, but never changes
# files/directories.  This equates to passing the <tt>:noop</tt> flag to methods
# in FileUtils.
#
# == module FileUtils::DryRun
#
# This module has all methods of FileUtils module, but never changes
# files/directories.  This equates to passing the <tt>:noop</tt> and
# <tt>:verbose</tt> flags to methods in FileUtils.
#

class RFileUtils{
	
	#
	# Options: mode noop verbose
	#
	# Creates a directory and all its parent directories.
	# For example,
	#
	#   FileUtils.mkdir_p '/usr/local/lib/ruby'
	#
	# causes to make following directories, if it does not exist.
	#     * /usr
	#     * /usr/local
	#     * /usr/local/lib
	#     * /usr/local/lib/ruby
	#
	# You can pass several directories at a time in a list.
	#
	/*
    def mkdir_p(list, options = {})
      fu_check_options options, OPT_TABLE['mkdir_p']
      list = fu_list(list)
      fu_output_message "mkdir -p #{options[:mode] ? ('-m %03o ' % options[:mode]) : ''}#{list.join ' '}" if options[:verbose]
      return *list if options[:noop]

      list.map {|path| path.sub(%r</\z>, '') }.each do |path|
        # optimize for the most common case
        begin
          fu_mkdir path, options[:mode]
          next
        rescue SystemCallError
          next if File.directory?(path)
        end

        stack = []
        until path == stack.last   # dirname("/")=="/", dirname("C:/")=="C:/"
          stack.push path
          path = File.dirname(path)
        end
        stack.reverse_each do |dir|
          begin
            fu_mkdir dir, options[:mode]
          rescue SystemCallError
            raise unless File.directory?(dir)
          end
        end
      end

      return *list
    end
	*/
		
	public static function mkdir_p($path){
		return @mkdir($path, 0777, true);
	}
	
	public static function rm_r($list, $options=array()){
		$force = $options['force'] ? 'f' : '';
		$list = join((array)$list, ' ');
		$command = "rm -r{$force} {$list} 2>&1";
		if($options['verbose']){
			echo $command;
		}
		@exec($command, $output);
		return count($output) == 0;
	}
	
	public static function rm_rf($list, $options=array()){
		$options = array('force' => true) + $options;
		return self::rm_r($list, $options);
	}
	
}
