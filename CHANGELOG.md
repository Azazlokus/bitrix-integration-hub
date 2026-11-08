# Changelog

## 1.0.0 (2026-11-08)


### Features

* **infra:** generate .settings_extra.php for Redis cache and sessions ([dcbcb3a](https://github.com/Azazlokus/bitrix-integration-hub/commit/dcbcb3a1a816aef1512315a2c4cc7bea3593683f))


### Bug Fixes

* **deps:** bump symfony/filesystem to fix security advisory ([6d30c35](https://github.com/Azazlokus/bitrix-integration-hub/commit/6d30c3566618c464b60f1b8ed34eeaff53fc845c))
* **infra:** apply stage 1 review findings ([c515c42](https://github.com/Azazlokus/bitrix-integration-hub/commit/c515c4212e46a8dd002ec8d1ad094a11356df8d0))
* **infra:** avoid double composer autoload in CLI bootstrap ([feeec72](https://github.com/Azazlokus/bitrix-integration-hub/commit/feeec727a62eddf99a21f73dc834b515ae8a90be))
* **infra:** print actual ports from .env in make banners ([fc40136](https://github.com/Azazlokus/bitrix-integration-hub/commit/fc401365b84e6c935e9bcc77f4ecb3959694e0f9))
* **security:** isolate networks, drop env_file, restrict tunnel ([3ece1a4](https://github.com/Azazlokus/bitrix-integration-hub/commit/3ece1a411696826a7f194d8817491897df6d0ed2))


### Documentation

* add README, progress tracker, DevSecOps guide and ADR 0001 ([7a4ecab](https://github.com/Azazlokus/bitrix-integration-hub/commit/7a4ecab72311348740f4d6b01a9d9b96023dd01e))
* **adr:** add roadmap v2 with Laravel Core as main backend ([00bea1a](https://github.com/Azazlokus/bitrix-integration-hub/commit/00bea1a6fe23ed9ee916a9f827b41603d4f29016))
* describe ProfSport legend in README and progress ([214ed36](https://github.com/Azazlokus/bitrix-integration-hub/commit/214ed368c87b509417bb1c0b26d04fc1c557e12f))
* **infra:** add ADR 0002 and update progress for stage 1 ([b40d289](https://github.com/Azazlokus/bitrix-integration-hub/commit/b40d2892a1e6601e02e5fe46f1ec183a8a0440bb))
* **infra:** correct stage 1 acceptance status in progress ([8ae1c54](https://github.com/Azazlokus/bitrix-integration-hub/commit/8ae1c5401a80b30c17eed8f5cab79da6046aae8f))
* **infra:** set passwords before first make up in README ([e4f935e](https://github.com/Azazlokus/bitrix-integration-hub/commit/e4f935e57dfa530f1225b1ca739e6fcff4d0e601))


### Build & Infrastructure

* **deps:** bump keycloak to 26.8.2 ([0f737e0](https://github.com/Azazlokus/bitrix-integration-hub/commit/0f737e0d88317193843fe7367eb2cff9808b98e1))
* **infra:** add .env.example and narrow env deny rule ([11f3005](https://github.com/Azazlokus/bitrix-integration-hub/commit/11f3005728a4d626fbc587f77c84381491712a4a))
* **infra:** add composer setup and autoload in init.php ([9d66a0f](https://github.com/Azazlokus/bitrix-integration-hub/commit/9d66a0f810d1c2e6ed71f004fc2b2f5ff81487a6))
* **infra:** add docker compose stack with nginx and mysql config ([4930221](https://github.com/Azazlokus/bitrix-integration-hub/commit/49302211945ceee2b7318a4ea5b25ba745a57c3b))
* **infra:** add make targets for stack, tests, tunnel and worker ([db90504](https://github.com/Azazlokus/bitrix-integration-hub/commit/db9050454731754219689fd432de1448fa2b0ca7))
* **infra:** add PHP 8.4 FPM and mock-services images ([df9ff78](https://github.com/Azazlokus/bitrix-integration-hub/commit/df9ff78dc780a1bce2f67780a5b8a9e2898cb722))
* **infra:** add PHPStan level 8 and PER-CS 3.0 configs ([1ea4ab5](https://github.com/Azazlokus/bitrix-integration-hub/commit/1ea4ab58a20fe20a7af296232d53a79bd64dcdc9))
