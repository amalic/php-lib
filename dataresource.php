<?php
/**
Copyright (C) 2013 Michel Dumontier

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
class DataResource
{
	public function __construct($rdf_factory)
	{
		if($rdf_factory === null) {
			trigger_error("you must provide an instance of the rdf factory",E_USER_ERROR);
			return false;
		}
		$this->setRDFFactory($rdf_factory);
	}
	function __destruct(){}
	
	public function setRDFFactory($rdf_factory){$this->rdf_factory=$rdf_factory;return $this;}
	public function getRDFFactory(){return $this->rdf_factory;}
	
	public function setURI($uri){$this->uri = $uri;return $this;}
	public function getURI(){return $this->uri;}
	
	public function setQName($qname) {
		$this->qname = $qname;
		$this->setURI($this->getRDFFactory()->getFQURI($qname));
		return $this;
	}
	public function getQName() {return @$this->qname;}
	
	public function setTitle($string){$this->title = $string;return $this;}
	public function getTitle() {return @$this->title;}

	public function setDescription($string){$this->description = $string;return $this;}
	public function getDescription() {return @$this->description;}
	
	public function setCreator($creator){$this->creator = $creator;return $this;}
	public function getCreator() {return @$this->creator;}
	
	public function setPublisher($publisher){$this->publisher = $publisher;return $this;}
	public function getPublisher() {return @$this->publisher;}

	public function setCreateDate($date){$this->create_date = $date;return $this;}
	public function getCreateDate() {return @$this->create_date;}		

	public function setRetrievedDate($date){$this->retrieved_date = $date;return $this;}
	public function getRetrievedDate(){return @$this->retrieved_date;}
	
	public function setIssuedDate($date){$this->issued_date = $date;return $this;}
	public function getIssuedDate() {return @$this->issued_date;}		

	public function setSource($source) {$this->source[] = $source;return $this;}
	public function getSource(){return @$this->source;}
	
	public function setVersion($string){$this->version = $string;return $this;}
	public function getVersion() {return @$this->version;}
	
	public function setHomepage($url){$this->homepage = $url;return $this;}
	public function getHomepage() {return @$this->homepage;}
	
	public function setLicense($url){$this->license = $url;return $this;}
	public function getLicense() {return @$this->license;}
	
	public function setFormat($string){$this->format[] = $string;return $this;}
	public function getFormat() {return @$this->format;}
	
	public function setDataset($url){$this->dataset = $url;return $this;}
	public function getDataset() {return @$this->dataset;}
	
	public function setLocation($url){$this->download = $url;return $this;}
	public function getLocation() {return @$this->download;}

	public function setRights($right){$this->rights[] = $right;return $this;}
	public function getRights(){return @$this->rights;}
	
	public function setStandardRights($rights) {$this->standard_rights = $rights;return $this;}
	public function getStandardRights()
	{
		$rights = array(
			"use" => "free to use",
			"use-share" => "free to use and share as is",
			"use-share-modify" => "free to use, share, modify",			
			"no-commercial" => "commercial use requires licensing",
			"no-derivative" => "no derivatives allowed without permission",
			"attribution" => "requires attribution",
			"restricted-by-source-license" => "check source for further restrictions"
		);
		if(!isset($rights[$right])) {
			trigger_error("Unable to find $right in ".implode(",",array_keys($rights))." of rights");
			return FALSE;
		}
		return $rights[$right];
	}	
	
	public function toRDF()
	{
		$rdf = '';
		$f = $this->getRDFFactory();
		
		$dataset_uri = $this->getURI();
		
		if($this->getTitle() !== null && $this->getTitle() !== '') {
			$label = $this->getTitle();
		} else {
			$label = 
				($this->getTitle() === null ? "Dataset":$this->getTitle())
				.($this->getPublisher() === null ? "":" generated by ".$this->getPublisher());
		}
		if($this->getCreateDate()) $label .= " generated at ".$this->getCreateDate();

		$rdf .= $f->QQuadL($dataset_uri,"rdfs:label",$label);
		$rdf .= $f->QQuad($dataset_uri,"rdf:type","dcat:Distribution");

		if($this->getTitle() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"dc:title",$this->getTitle());
		}
		if($this->getDescription() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"dc:description",$this->getDescription());
		}		
		if($this->getCreateDate() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"dc:created",$this->getCreateDate(),null,"xsd:dateTime");
		}
		if($this->getIssuedDate() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"dc:issued",$this->getIssuedDate(),null,"xsd:dateTime");
		}
		if($this->getRetrievedDate() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"pav:retrievedOn",$this->getRetrievedDate(),null,"xsd:dateTime");
		}
		if($this->getSource() !== null) {
			foreach($this->getSource() AS $source) {
				if(!empty($source)) 
 				 $rdf .= (strstr($source,"://")?
					$f->QQuadO_URL($dataset_uri,"dc:source",$source)
					:$f->QQuadL($dataset_uri,"dc:source",$source));
			}
		}		
		if($this->getCreator() !== null) {
			$rdf .= (strstr($this->getCreator(),"://")?
				$f->QQuadO_URL($dataset_uri,"dc:creator",$this->getCreator())
				:$f->QQuadL($dataset_uri,"dc:creator",$this->getCreator()));
		}
		if($this->getPublisher() !== null) {
			$rdf .= (strstr($this->getPublisher(),"://")?
				$f->QQuadO_URL($dataset_uri,"dc:publisher",$this->getPublisher())
				:$f->QQuadL($dataset_uri,"dc:publisher",$this->getPublisher()));
		}
		if($this->getHomepage() !== null) {
			$rdf .= $f->QQuadO_URL($dataset_uri,"foaf:page",$this->getHomepage());
		}
		if($this->getVersion() !== null) {
			$rdf .= $f->QQuadL($dataset_uri,"pav:version",(string)$this->getVersion());
		}
		if($this->getFormat() !== null) {
			$rdf_formats = array("rdf","n-triples","n3","turtle","ttl");
			foreach($this->getFormat() AS $format) {
				foreach($rdf_formats AS $rf) {
					if(strstr($rf,$format)) {
						$rdf .= $f->QQuad($dataset_uri,"rdf:type","void:Dataset");
					}
				}
				$rdf .= $f->QQuadL($dataset_uri,"dc:format",$format);
			}
		}
		
		if($this->getDataset() !== null) {
			$rdf .= $f->QQuadO_URL($this->getDataset(), "dcat:distribution",$dataset_uri);
			$rdf .= $f->QQuad($this->getDataset(), "rdf:type", "dc:Dataset");
		}
		
		if($this->getLicense() !== null) {
			$rdf .= $f->QQuadO_URL($dataset_uri,"dc:license",$this->getLicense());
		}
		if($this->getRights() !== null) {
			foreach($this->getRights() AS $right) {
				$rdf .= $f->QQuadL($dataset_uri,"dc:rights",$right);
			}
		}
		return $rdf;
	}
}
