<?php

	namespace MehrIt\EasyJson\Contracts;

	use MehrIt\EasyJson\Build\JsonBuilder;

	interface JsonSerializable
	{
		/**
		 * Serializes the instance using the given builder
		 * @param JsonBuilder $builder The builder
		 */
		public function serializeJson(JsonBuilder $builder);
	}