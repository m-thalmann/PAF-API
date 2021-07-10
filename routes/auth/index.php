<?php

namespace API\Routes;

use API\Auth\Authorization;
use API\Config\Config;
use API\Models\User;
use PAF\Model\DuplicateException;
use PAF\Model\InvalidException;
use PAF\Router\Response;

$group
    ->get('/', Authorization::middleware())
    ->post('/login', function ($req) {
        if ($req["post"]["email"] && $req["post"]["password"]) {
            $token = Authorization::login(
                $req["post"]["email"],
                $req["post"]["password"]
            );

            if ($token) {
                return Response::ok([
                    "info" => "Authorized",
                    "user" => Authorization::user(),
                    "token" => $token,
                ]);
            } else {
                return Response::notFound([
                    "info" => "Email or password wrong",
                ]);
            }
        } else {
            return Response::badRequest([
                "info" => "Email and password expected",
            ]);
        }
    })
    ->post('/register', function ($req) {
        if (!Config::get("registration_enabled", true)) {
            return Response::methodNotAllowed([
                "info" => "Registration is disabled",
            ]);
        }

        $user = User::fromValues($req["post"] ? $req["post"] : []);

        try {
            $user->save();
        } catch (InvalidException $e) {
            return Response::badRequest($user->getErrors());
        } catch (DuplicateException $e) {
            return Response::conflict("A user with this email already exists");
        }

        return Response::created([
            "info" => "Authorized",
            "user" => $user,
            "token" => Authorization::generateToken($user),
        ]);
    });
