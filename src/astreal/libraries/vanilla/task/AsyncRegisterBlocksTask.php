<?php
declare(strict_types=1);

namespace astreal\libraries\vanilla\task;

use astreal\libraries\vanilla\block\BlockFactory;
use pmmp\thread\ThreadSafeArray;
use pocketmine\block\Block;
use pocketmine\scheduler\AsyncTask;

final class AsyncRegisterBlocksTask extends AsyncTask {

	private ThreadSafeArray $blockFuncs;
	private ThreadSafeArray $serializer;
	private ThreadSafeArray $deserializer;

	/**
	 * @param Closure[] $blockFuncs
	 */
	public function __construct(private string $cachePath, array $blockFuncs){
		$this->blockFuncs = new ThreadSafeArray();
		$this->serializer = new ThreadSafeArray();
		$this->deserializer = new ThreadSafeArray();
		foreach($blockFuncs as $identifier => [$blockFunc, $serializer, $deserializer]){
			$this->blockFuncs[$identifier] = igbinary_serialize($blockFunc());
			$this->serializer[$identifier] = $serializer;
			$this->deserializer[$identifier] = $deserializer;
		}
	}

	public function onRun():void{
		foreach($this->blockFuncs as $identifier => $blockFunc)BlockFactory::getInstance()->registerBlock(fn(): Block => igbinary_unserialize($blockFunc), $identifier, serializer: $this->serializer[$identifier], deserializer: $this->deserializer[$identifier]);
	}
}