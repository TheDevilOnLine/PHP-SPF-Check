<?php
/**
 *
 * @author Mikael Peigney
 */

namespace Mika56\SPFCheck;


use Mika56\SPFCheck\Exception\DNSLookupException;

class DNSRecordGetter implements DNSRecordGetterInterface
{
    /**
     * @param $domain The domain to get SPF record
     * @return string|false The SPF record, or false if there is no SPF record
     * @throws DNSLookupException
     */
    public function getSPFRecordForDomain($domain)
    {
        $records = dns_get_record($domain, DNS_TXT | DNS_SOA);
        if (false === $records || (is_array($records) && empty($records))) {
            throw new DNSLookupException;
        }

        foreach ($records as $record) {
            if (array_key_exists('txt', $record)) {
                $txt = $record['txt'];
                if (stripos($txt, 'v=spf1') === 0) {
                    return $txt;
                }
            }
        }

        return false;
    }

    public function resolveA($domain)
    {
        $records = dns_get_record($domain, DNS_A | DNS_AAAA);
        if (false === $records || (is_array($records) && empty($records))) {
            throw new DNSLookupException;
        }

        $addresses = [];

        foreach ($records as $record) {
            if (array_key_exists('a', $record)) {
                $addresses[] = $record['a'];
            } elseif (array_key_exists('aaaa', $record)) {
                $addresses[] = $record['aaaa'];
            }
        }

        return $addresses;
    }

    public function resolveMx($domain)
    {
        $records = dns_get_record($domain, DNS_MX);
        if (false === $records || (is_array($records) && empty($records))) {
            throw new DNSLookupException;
        }

        $addresses = [];

        foreach ($records as $record) {
            if (array_key_exists('mx', $record)) {
                $addresses[] = $record['mx'];
            }
        }

        return $addresses;
    }

    public function resolvePtr($ipAddress)
    {
        // PHP does not seem to be able to get multiple PTR?
        return [gethostbyaddr($ipAddress)];
    }

    public function exists($domain)
    {
        return checkdnsrr($domain, 'A');
    }
}