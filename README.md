Guardian Agent
==============

Guardian Agent is a simple system monitoring agent.

It periodically executes "checks". A check can be any executable, as long as it returns status codes:

* 0 OK: The plugin was able to check the service and it appeared to be functioning properly
* 1 Warning: The plugin was able to check the service, but it appeared to be above some "warning" threshold or did not appear to be working properly
* 2 Critical: The plugin detected that either the service was not running or it was above some "critical" threshold
* other: Unknown

If you are familiar with Nagios, Sensu or Icinga are probably familiar with this approach.

## Configuration

You configure the agent using a `guardian.yml` file. Please check the `guardian.yml.dist` file for an example.

The config file can contain a list of "checks". For example:

```yml
checks:
    check_load_average:
        command: "check_load -w 1 -c 2"
        interval: 3
        hosts: web, app
        
    check_disk_space_available:
        command: "check_disk -w 100 -c 200"
        interval: 5
```

Every check gets a unique "name". In this you are creating 2 checks: `check_load_average` and `check_disk_space_available`.

Each check specifies a `command` to execute. As we're following the standard exit-code based checks, you can use any existing
Nagios-compatible check (You can find existing checks on https://exchange.nagios.org/ for example.)

Using the `interval` key, you can specify how many seconds between these checks.

Use the `hosts` key to limit a check to only hosts that are part of the listed groups (advanced).


## License

MIT. Please refer to the [license file](LICENSE.md) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
