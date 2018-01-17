'use strict';

class Merger {

	constructor() {
		this._context = this._createContext();
	}

	_createContext() {
		window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext;
		return new AudioContext();
	}

	async fetchAudio(...filepaths) {
		const files = filepaths.map(async filepath =>  {
			const buffer = await fetch(filepath).then(response => response.arrayBuffer());
			return await this._context.decodeAudioData(buffer);
		});
		return await Promise.all(files);
	}

	concatAndExport(buffers, audioType, fname = "") {
		let output = this._context.createBuffer(1, 44100*this._totalDuration(buffers), 44100),
			offset = 0;
		buffers.map(buffer => {
			output.getChannelData(0).set(buffer.getChannelData(0), offset);
			offset += buffer.length;
		});
		const type = audioType || 'audio/mp3';
		const recorded = this._interleave(output);
		const dataview = this._writeHeaders(recorded);
		const audioBlob = new Blob([dataview], { type: type });

		const name = fname + 'auragrama';
		const a = document.createElement("a");
		//a.style = "display: none";
		a.href = this._renderURL(audioBlob);
        a.download = `${name}.${audioBlob.type.split('/')[1]}`;
		document.body.appendChild(a);
		a.click();
	}


	_maxDuration(buffers) {
		return Math.max.apply(Math, buffers.map(buffer => buffer.duration));
	}

	_totalDuration(buffers) {
		return buffers.map(buffer => buffer.duration).reduce((a, b) => a + b, 0);
	}

	_isSupported() {
		return 'AudioContext' in window;
	}

	_writeHeaders(buffer) {
		let arrayBuffer = new ArrayBuffer(44 + buffer.length * 2),
			view = new DataView(arrayBuffer);

		this._writeString(view, 0, 'RIFF');
		view.setUint32(4, 32 + buffer.length * 2, true);
		this._writeString(view, 8, 'WAVE');
		this._writeString(view, 12, 'fmt ');
		view.setUint32(16, 16, true);
		view.setUint16(20, 1, true);
		view.setUint16(22, 2, true);
		view.setUint32(24, 44100, true);
		view.setUint32(28, 44100 * 4, true);
		view.setUint16(32, 4, true);
		view.setUint16(34, 16, true);
		this._writeString(view, 36, 'data');
		view.setUint32(40, buffer.length * 2, true);

		return this._floatTo16BitPCM(view, buffer, 44);
	}

	_floatTo16BitPCM(dataview, buffer, offset) {
		for (var i = 0; i < buffer.length; i++, offset+=2){
			let tmp = Math.max(-1, Math.min(1, buffer[i]));
			dataview.setInt16(offset, tmp < 0 ? tmp * 0x8000 : tmp * 0x7FFF, true);
		}
		return dataview;
	}

	_writeString(dataview, offset, header) {
		let output;
		for (var i = 0; i < header.length; i++){
			dataview.setUint8(offset + i, header.charCodeAt(i));
		}
	}

	_interleave(input) {
		let buffer = input.getChannelData(0),
			length = buffer.length*2,
			result = new Float32Array(length),
			index = 0, inputIndex = 0;

		while (index < length){
			result[index++] = buffer[inputIndex];
			result[index++] = buffer[inputIndex];
			inputIndex++;
		}
		return result;
	}

	_renderURL(blob) {
		return (window.URL || window.webkitURL).createObjectURL(blob);
	}

}
