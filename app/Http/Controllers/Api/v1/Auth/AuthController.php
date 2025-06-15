<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Auth;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Application\QueryBus\QueryBusInterface;
use App\Application\Query\Auth\GetUserByIdQuery;
use App\Application\CommandBus\CommandBusInterface;
use App\Application\Command\Auth\VerifyEmailCommand;
use App\Application\Command\Auth\VerifyPhoneCommand;
use App\Application\Command\Auth\ResetPasswordCommand;
use App\Application\Command\Auth\VerifyTelegramCommand;
use App\Application\Command\Auth\LoginUserByEmailCommand;
use App\Application\Command\Auth\LoginUserByPhoneCommand;
use App\Application\Command\Auth\LoginUserByTelegramCommand;
use App\Application\Command\Auth\RequestPasswordResetCommand;
use App\Application\Command\Registration\RegisterUserByEmailCommand;
use App\Application\Command\Registration\RegisterUserByPhoneCommand;
use App\Application\Command\Registration\RegisterUserByTelegramCommand;

class AuthController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus
    ) {}

    public function registerByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $command = new RegisterUserByEmailCommand(
                $request->input('email'),
                $request->input('password'),
                $request->input('first_name'),
                $request->input('last_name')
            );

            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован. Код верификации отправлен на ваш Email.',
                'user' => $userDto
            ], 201);
        } catch (Throwable $e) {
            Log::error('Ошибка при регистрации пользователя: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }


    public function registerByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $command = new RegisterUserByPhoneCommand(
                $request->input('phone'),
                $request->input('password'),
                $request->input('first_name'),
                $request->input('last_name')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован по номеру телефона. Код верификации отправлен.',
                'user' => $userDto
            ], 201);
        } catch (Throwable $e) {
            Log::error('Ошибка при регистрации пользователя по телефону: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function registerByTelegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => 'required|string|unique:users,telegram_id',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $command = new RegisterUserByTelegramCommand(
                $request->input('telegram_id'),
                $request->input('password'),
                $request->input('first_name'),
                $request->input('last_name')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован по Telegram ID. Код верификации отправлен.',
                'user' => $userDto
            ], 201);
        } catch (Throwable $e) {
            Log::error('Ошибка при регистрации пользователя по Telegram: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function loginByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        try {
            $command = new LoginUserByEmailCommand(
                $request->input('email'),
                $request->input('password')
            );
            $authResultDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Вход выполнен успешно.',
                'data' => $authResultDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при входе пользователя: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function loginByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $command = new LoginUserByPhoneCommand(
                $request->input('phone'),
                $request->input('password')
            );
            $authResultDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Вход выполнен успешно.',
                'data' => $authResultDto,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка при входе пользователя по телефону: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function loginByTelegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $command = new LoginUserByTelegramCommand(
                $request->input('telegram_id'),
                $request->input('password')
            );
            $authResultDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Вход выполнен успешно.',
                'data' => $authResultDto,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка при входе пользователя по Telegram ID: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|digits:6',
        ]);

        try {
            $command = new VerifyEmailCommand(
                $request->input('email'),
                $request->input('code')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Email успешно верифицирован.',
                'user' => $userDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при верификации Email: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|digits:6',
        ]);

        try {
            $command = new VerifyPhoneCommand(
                $request->input('phone'),
                $request->input('code')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Номер телефона успешно верифицирован.',
                'user' => $userDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при верификации номера телефона: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function verifyTelegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => 'required|string',
            'code' => 'required|string|digits:6',
        ]);

        try {
            $command = new VerifyTelegramCommand(
                $request->input('telegram_id'),
                $request->input('code')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Telegram успешно верифицирован.',
                'user' => $userDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при верификации Telegram: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $command = new RequestPasswordResetCommand(
                $request->input('email')
            );
            $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Инструкции по сбросу пароля отправлены на ваш Email.',
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при запросе сброса пароля: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|digits:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $command = new ResetPasswordCommand(
                $request->input('email'),
                $request->input('code'),
                $request->input('new_password')
            );
            $userDto = $this->commandBus->dispatch($command);

            return response()->json([
                'success' => true,
                'message' => 'Пароль успешно сброшен. Вы можете войти с новым паролем.',
                'user' => $userDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при сбросе пароля: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $userId = $request->user()->id->toString();

        try {
            $query = new GetUserByIdQuery($userId);
            $userDto = $this->queryBus->ask($query);

            return response()->json([
                'success' => true,
                'user' => $userDto
            ]);
        } catch (Throwable $e) {
            Log::error('Ошибка при получении данных пользователя: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла внутренняя ошибка сервера.'
            ], 500);
        }
    }
}
