<?php
/*
Plugin Name: NextGEN Gallery Sidebar Widget
Plugin URI: http://ailoo.net/2009/04/nextgen-gallery-sidebar-widget/
Description: A widget to show random galleries from NGG with preview image.
Author: Mathias Geat
Version: 0.4.3
Author URI: http://ailoo.net/
*/

/**
 * Changelog
 * ---------
 *
 * 0.4              Add include_galleries option
 * 0.3.3.1          Fix bug on widget no displaying galleries when no exclusions are set (bug #59, #60)
 * 0.3.3            Fix wrong maximum galleries (bug #58)
 * 0.3.2            Image output width fix in template
 *                  Cleanup
 * 0.3.1            Gallery limit bugfix
 * 0.3              Wordpress 2.8+ Widget API
 *                  Gallery exclusion option
 *                  Templating feature
 * 0.2.2            Add gallery_thumbnail option to select thumbnail image (preview, first, random)
 */

add_action('widgets_init', create_function('', 'return register_widget("NextGEN_Gallery_Sidebar_Widget");'));

class NextGEN_Gallery_Sidebar_Widget extends WP_Widget
{
    protected $_templates = array();

    function NextGEN_Gallery_Sidebar_Widget()
    {
        $widget_ops = array('classname' => 'ngg-sidebar-widget', 'description' => 'A widget to show random galleries with preview image.');
        $this->WP_Widget('ngg-sidebar-widget', 'NextGEN Gallery Sidebar Widget', $widget_ops);
    }

    function widget($args, $instance)
    {
        global $wpdb;
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);

        switch ($instance['gallery_order']) {
            case 'added_desc':
                $order = 'gid DESC';
                break;
            case 'added_asc':
                $order = 'gid ASC';
                break;
            default:
                $order = 'RAND()';
                break;
        }

        $included_galleries = $this->explode_ids($instance['included_galleries']);
        $excluded_galleries = $this->explode_ids($instance['excluded_galleries']);

        $where = ' ';
        if (count($included_galleries) > 0) {
            $include = array();
            foreach ($included_galleries as $included_gallery) {
                if (!in_array($included_gallery, $excluded_galleries)) {
                    $include[] = $included_gallery;
                }
            }

            $where = " WHERE gid IN (" . implode(',', $include) . ")";
        } else if (count($excluded_galleries) > 0) {
            $where = " WHERE gid NOT IN (" . implode(',', $excluded_galleries) . ")";
        }

        $results = $wpdb->get_results("SELECT * FROM $wpdb->nggallery" . $where . " ORDER BY " . $order . " LIMIT 0, " . $instance['max_galleries']);
        if (is_array($results) && count($results) > 0) {
            $galleries = array();
            foreach ($results as $result) {
                if ($wpdb->get_var("SELECT COUNT(pid) FROM $wpdb->nggpictures WHERE galleryid = '" . $result->gid . "'") > 0) {
                    if ($instance['gallery_thumbnail'] == 'preview' && (int) $result->previewpic > 0) {
                        // ok
                    } elseif ($instance['gallery_thumbnail'] == 'random') {
                        $result->previewpic = $wpdb->get_var("SELECT pid FROM $wpdb->nggpictures WHERE galleryid = '" . $result->gid . "' ORDER BY RAND() LIMIT 1");
                    } else {
                        // else take the first image
                        $result->previewpic = $wpdb->get_var("SELECT pid FROM $wpdb->nggpictures WHERE galleryid = '" . $result->gid . "' ORDER BY sortorder ASC, pid ASC LIMIT 1");
                    }

                    $galleries[] = $result;
                }
            }

            if (count($galleries) > 0) {
                $outerTplFile = get_template_directory() . '/ngg-sidebar-widget/tpl.outer.html';
                $innerTplFile = get_template_directory() . '/ngg-sidebar-widget/tpl.inner.html';

                $outerTplFile = (file_exists($outerTplFile)) ? $outerTplFile : dirname(__FILE__) . '/tpl/tpl.outer.html';
                $innerTplFile = (file_exists($innerTplFile)) ? $innerTplFile : dirname(__FILE__) . '/tpl/tpl.inner.html';

                $outerTpl = file_get_contents($outerTplFile);
                $innerTpl = file_get_contents($innerTplFile);

                if (empty($outerTpl)) {
                    $outerTpl = '{=inner}';
                }

                $this->parseTemplate('innerTpl', $innerTpl);

                $output = "\n";
                $output .= $args['before_widget'] . "\n";
                $output .= $args['before_title'] . $title . $args['after_title'] . "\n";

                $innerOutput = '';

                foreach ($galleries as $gallery) {
                    $imagerow = $wpdb->get_row("SELECT * FROM $wpdb->nggpictures WHERE pid = '" . $gallery->previewpic . "'");
                    foreach ($gallery as $key => $value) {
                        $imagerow->$key = $value;
                    }

                    $image = new nggImage($imagerow);

                    $tpl = array(
                        'gallery' => (array) $gallery,
                        'image' => (array) $image
                    );

                    if ($gallery->pageid > 0) {
                        $gallery_link = get_permalink($gallery->pageid);
                    } elseif (!empty($instance['default_link'])) {
                        $gallery_link = get_permalink($instance['default_link']);
                    } else {
                        $gallery_link = get_permalink(1);
                    }

                    $tpl['gallery']['link'] = $gallery_link;

                    if (function_exists('getphpthumburl') && trim($instance['autothumb_params']) != '') {
                        $tpl['image']['url'] = getphpthumburl($image->imageURL, $instance['autothumb_params']);
                    } else {
                        $tpl['image']['url'] = $image->thumbURL;
                    }

                    $tpl['image']['output_width'] = $instance['output_width'];
                    $tpl['image']['output_height'] = $instance['output_height'];

                    if (trim($instance['autothumb_params']) != '') {
                        $tpl['image']['output_width_tag'] = '';
                        $tpl['image']['output_height_tag'] = '';
                    } else {
                        $tpl['image']['output_width_tag'] = ' width="' . $instance['output_width'] . '"';
                        $tpl['image']['output_height_tag'] = ' height="' . $instance['output_height'] . '"';
                    }

                    $innerOutput .= $this->renderTemplate('innerTpl', $tpl);
                }

                $output .= str_replace('{=inner}', $innerOutput, $outerTpl);
                $output .= "\n" . $args['after_widget'] . "\n";
                echo $output;
            }
        }
    }

    function explode_ids($string, $separator = ',')
    {
        $ret = array();
        $exploded = explode($separator, $string);
        foreach ($exploded as $ex) {
            $ex = trim($ex);
            if (is_numeric($ex)) {
                $ret[] = $ex;
            }
        }

        return $ret;
    }

    function renderTemplate($id, $values)
    {
        $output = '';
        if (isset($this->_templates[$id])) {
            $output = $this->_templates[$id]['template'];
            foreach ($this->_templates[$id]['tags'] as $identifier => $val) {
                if (isset($values[$val[0]][$val[1]])) {
                    $output = str_replace('{=' . $identifier . '}', $values[$val[0]][$val[1]], $output);
                }
            }
        }

        return $output;
    }

    function parseTemplate($id, $template)
    {
        $tags = array();
        $pattern = '#\{\=([a-zA-Z0-9\-\_\.]*)\.([a-zA-Z0-9\-\_\.]*)\}#';
        preg_match_all($pattern, $template, $matches);

        if (is_array($matches) && count($matches) > 0) {
            foreach ($matches[0] as $key => $value) {
                $identifier = $matches[1][$key] . '.' . $matches[2][$key];
                $tags[$identifier][0] = $matches[1][$key];
                $tags[$identifier][1] = $matches[2][$key];
            }
        }

        $this->_templates[$id]['template'] = $template;
        $this->_templates[$id]['tags'] = $tags;
    }

    function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array(
            'title' => 'Galleries',
            'max_galleries' => 6,
            'gallery_order' => 'random',
            'gallery_thumbnail' => 'first',
            'autothumb_params' => '',
            'output_width' => 100,
            'output_height' => 75,
            'default_link' => 1,
            'included_galleries' => '',
            'excluded_galleries' => ''
                ));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title</label><br />
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('max_galleries'); ?>">Maximum Galleries</label><br />
            <input type="text" id="<?php echo $this->get_field_id('max_galleries'); ?>" name="<?php echo $this->get_field_name('max_galleries'); ?>" value="<?php echo $instance['max_galleries'] ?>" />
        <p>
            <label for="<?php echo $this->get_field_id('gallery_order'); ?>">Gallery Order</label><br />
            <select id="<?php echo $this->get_field_name('gallery_order'); ?>" name="<?php echo $this->get_field_name('gallery_order'); ?>">';
                <option value="random" <?php echo ($instance['gallery_order'] == 'random') ? ' selected="selected"' : ''; ?>>Random</option>
                <option value="added_asc" <?php echo ($instance['gallery_order'] == 'added_asc') ? ' selected="selected"' : ''; ?>>Date added ASC</option>
                <option value="added_desc" <?php echo ($instance['gallery_order'] == 'added_desc') ? ' selected="selected"' : ''; ?>>Date added DESC</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('gallery_thumbnail'); ?>">Gallery thumbnail image</label><br />
            <select id="<?php echo $this->get_field_name('gallery_thumbnail'); ?>" name="<?php echo $this->get_field_name('gallery_thumbnail'); ?>">';
                <option value="preview" <?php echo ($instance['gallery_thumbnail'] == 'preview') ? ' selected="selected"' : ''; ?>>Gallery Preview (set in NGG)</option>
                <option value="first" <?php echo ($instance['gallery_thumbnail'] == 'first') ? ' selected="selected"' : ''; ?>>First</option>
                <option value="random" <?php echo ($instance['gallery_thumbnail'] == 'random') ? ' selected="selected"' : ''; ?>>Random</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('autothumb_params'); ?>">Autothumb Parameters (if installed)</label><br />
            <input type="text" id="<?php echo $this->get_field_id('autothumb_params'); ?>" name="<?php echo $this->get_field_name('autothumb_params'); ?>" value="<?php echo $instance['autothumb_params'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('output_width'); ?>">Output width</label><br />
            <input type="text" id="<?php echo $this->get_field_id('output_width'); ?>" name="<?php echo $this->get_field_name('output_width'); ?>" value="<?php echo $instance['output_width'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('output_height'); ?>">Output height</label><br />
            <input type="text" id="<?php echo $this->get_field_id('output_height'); ?>" name="<?php echo $this->get_field_name('output_height'); ?>" value="<?php echo $instance['output_height'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('default_link'); ?>">Default Link Id (galleries without image page)</label><br />
            <input type="text" id="<?php echo $this->get_field_id('default_link'); ?>" name="<?php echo $this->get_field_name('default_link'); ?>" value="<?php echo $instance['default_link'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('included_galleries'); ?>">Included gallery IDs (comma separated)</label><br />
            <input type="text" id="<?php echo $this->get_field_id('included_galleries'); ?>" name="<?php echo $this->get_field_name('included_galleries'); ?>" value="<?php echo $instance['included_galleries'] ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('excluded_galleries'); ?>">Excluded gallery IDs (comma separated)</label><br />
            <input type="text" id="<?php echo $this->get_field_id('excluded_galleries'); ?>" name="<?php echo $this->get_field_name('excluded_galleries'); ?>" value="<?php echo $instance['excluded_galleries'] ?>" />
        </p>
        <?php
    }

    function update($new_instance, $old_instance)
    {
        $new_instance['title'] = htmlspecialchars($new_instance['title']);
        return $new_instance;
    }
}
